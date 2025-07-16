<?php

namespace App\Http\Controllers\admin_panel;

use App\Models\Contest;
use App\Models\Campus;
use App\Models\Classroom;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ContestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }
    /**
     * Create a new contest
     */
    public function CreateContest(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after:now',
            'duration' => 'required|integer|min:1|max:480', // max 8 hours
            'number_of_questions' => 'required|integer|min:1|max:100',
            'campus_ids' => 'nullable|array',
            'campus_ids.*' => 'exists:campuses,id',
            'classroom_ids' => 'nullable|array',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        try {
            DB::beginTransaction();

            // Create the contest
            $contest = Contest::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'date' => $validatedData['date'],
                'duration' => $validatedData['duration'],
                'number_of_questions' => $validatedData['number_of_questions'],
            ]);

            // Associate campuses if provided
            if (isset($validatedData['campus_ids'])) {
                $contest->campuses()->attach($validatedData['campus_ids']);
            }

            // Associate classrooms if provided
            if (isset($validatedData['classroom_ids'])) {
                $contest->classrooms()->attach($validatedData['classroom_ids']);
            }

            DB::commit();

            // Load relationships for response
            $contest->load(['campuses', 'classrooms']);

            return response()->json([
                'message' => 'Contest created successfully.',
                'contest' => $contest,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error creating contest.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing contest
     */
    public function UpdateContest(Request $request, $id)
    {
        // Find the contest by ID
        $contest = Contest::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'date' => 'sometimes|required|date|after:now',
            'duration' => 'sometimes|required|integer|min:1|max:480',
            'number_of_questions' => 'sometimes|required|integer|min:1|max:100',
            'campus_ids' => 'sometimes|nullable|array',
            'campus_ids.*' => 'exists:campuses,id',
            'classroom_ids' => 'sometimes|nullable|array',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        try {
            DB::beginTransaction();

            // Update the contest with the validated data
            $contest->update([
                'name' => $validatedData['name'] ?? $contest->name,
                'description' => $validatedData['description'] ?? $contest->description,
                'date' => $validatedData['date'] ?? $contest->date,
                'duration' => $validatedData['duration'] ?? $contest->duration,
                'number_of_questions' => $validatedData['number_of_questions'] ?? $contest->number_of_questions,
            ]);

            // Update campus associations if provided
            if (isset($validatedData['campus_ids'])) {
                $contest->campuses()->sync($validatedData['campus_ids']);
            }

            // Update classroom associations if provided
            if (isset($validatedData['classroom_ids'])) {
                $contest->classrooms()->sync($validatedData['classroom_ids']);
            }

            DB::commit();

            // Load relationships for response
            $contest->load(['campuses', 'classrooms']);

            return response()->json([
                'message' => 'Contest updated successfully.',
                'contest' => $contest,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error updating contest.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a contest
     */
    public function DeleteContest($id)
    {
        // Find the contest by ID
        $contest = Contest::findOrFail($id);

        try {
            DB::beginTransaction();

            // Remove relationships first
            $contest->campuses()->detach();
            $contest->classrooms()->detach();

            // Delete the contest record
            $contest->delete();

            DB::commit();

            return response()->json([
                'message' => 'Contest deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error deleting contest.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all contests with pagination
     */
    public function ListContests(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Retrieve contests with relationships
        $contests = Contest::with(['campuses', 'classrooms', 'participations'])
            ->orderBy('date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'contests' => $contests,
        ], 200);
    }

    /**
     * Get a specific contest by ID
     */
    public function GetContest($id)
    {
        // Find the contest by ID with relationships
        $contest = Contest::with([
            'campuses', 
            'classrooms', 
            'participations', 
            'contest_phase_links',
            'certificates'
        ])->findOrFail($id);

        return response()->json([
            'contest' => $contest,
        ], 200);
    }

    /**
     * Search contests based on various criteria
     */
    public function SearchContests(Request $request)
    {
        // Validate the search parameters
        $validatedData = $request->validate([
            'query' => 'nullable|string|max:255',
            'status' => 'nullable|in:upcoming,ongoing,finished',
            'campus_id' => 'nullable|exists:campuses,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Contest::with(['campuses', 'classrooms']);

        // Search by name or description
        if (!empty($validatedData['query'])) {
            $searchQuery = $validatedData['query'];
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }

        // Filter by status
        if (!empty($validatedData['status'])) {
            $now = Carbon::now();
            switch ($validatedData['status']) {
                case 'upcoming':
                    $query->where('date', '>', $now);
                    break;
                case 'ongoing':
                    $query->where('date', '<=', $now)
                          ->where(DB::raw('DATE_ADD(date, INTERVAL duration MINUTE)'), '>=', $now);
                    break;
                case 'finished':
                    $query->where(DB::raw('DATE_ADD(date, INTERVAL duration MINUTE)'), '<', $now);
                    break;
            }
        }

        // Filter by campus
        if (!empty($validatedData['campus_id'])) {
            $query->whereHas('campuses', function ($q) use ($validatedData) {
                $q->where('campus_id', $validatedData['campus_id']);
            });
        }

        // Filter by classroom
        if (!empty($validatedData['classroom_id'])) {
            $query->whereHas('classrooms', function ($q) use ($validatedData) {
                $q->where('classroom_id', $validatedData['classroom_id']);
            });
        }

        // Filter by date range
        if (!empty($validatedData['date_from'])) {
            $query->where('date', '>=', $validatedData['date_from']);
        }
        if (!empty($validatedData['date_to'])) {
            $query->where('date', '<=', $validatedData['date_to']);
        }

        $contests = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'contests' => $contests,
        ], 200);
    }

    /**
     * Get contest statistics
     */
    public function GetContestStats($id)
    {
        $contest = Contest::with(['participations', 'campuses', 'classrooms'])
            ->findOrFail($id);

        $stats = [
            'total_participants' => $contest->participations->count(),
            'completed_participations' => $contest->participations->where('status', 'completed')->count(),
            'pending_participations' => $contest->participations->where('status', 'pending')->count(),
            'associated_campuses' => $contest->campuses->count(),
            'associated_classrooms' => $contest->classrooms->count(),
            'duration_formatted' => $this->formatDuration($contest->duration),
            'status' => $this->getContestStatus($contest),
        ];

        return response()->json([
            'contest' => $contest,
            'stats' => $stats,
        ], 200);
    }

    /**
     * Associate or disassociate campuses with a contest
     */
    public function ManageCampusAssociation(Request $request, $id)
    {
        $contest = Contest::findOrFail($id);

        $validatedData = $request->validate([
            'campus_ids' => 'required|array',
            'campus_ids.*' => 'exists:campuses,id',
        ]);

        try {
            $contest->campuses()->sync($validatedData['campus_ids']);
            $contest->load('campuses');

            return response()->json([
                'message' => 'Campus associations updated successfully.',
                'contest' => $contest,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating campus associations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associate or disassociate classrooms with a contest
     */
    public function ManageClassroomAssociation(Request $request, $id)
    {
        $contest = Contest::findOrFail($id);

        $validatedData = $request->validate([
            'classroom_ids' => 'required|array',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        try {
            $contest->classrooms()->sync($validatedData['classroom_ids']);
            $contest->load('classrooms');

            return response()->json([
                'message' => 'Classroom associations updated successfully.',
                'contest' => $contest,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating classroom associations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to format duration in minutes to human-readable format
     */
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $remainingMinutes . 'm';
        }
        return $remainingMinutes . 'm';
    }

    /**
     * Helper method to determine contest status
     */
    private function getContestStatus($contest)
    {
        $now = Carbon::now();
        $startTime = Carbon::parse($contest->date);
        $endTime = $startTime->copy()->addMinutes($contest->duration);

        if ($now->lt($startTime)) {
            return 'upcoming';
        } elseif ($now->between($startTime, $endTime)) {
            return 'ongoing';
        } else {
            return 'finished';
        }
    }
}
