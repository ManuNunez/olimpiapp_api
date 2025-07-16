<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Models\Contest;
use App\Models\Student;
use App\Models\ParticipationAnswer;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParticipationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'student']);
    }

    /**
     * Get available contests for the authenticated student
     */
    public function GetAvailableContests(Request $request)
    {
        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        // Get contests that are available (future dates) and not already participated in
        $now = Carbon::now();
        $participatedContestIds = $student->participations()
            ->pluck('contest_id')
            ->toArray();

        $availableContests = Contest::with(['campuses', 'classrooms'])
            ->where('date', '>', $now)
            ->whereNotIn('id', $participatedContestIds)
            ->orderBy('date', 'asc')
            ->get();

        // Add status and time information
        $availableContests->each(function ($contest) use ($now) {
            $contest->status = $this->getContestStatus($contest, $now);
            $contest->time_until_start = $this->getTimeUntilStart($contest, $now);
        });

        return response()->json([
            'student' => $student,
            'available_contests' => $availableContests,
        ], 200);
    }

    /**
     * Register for a contest
     */
    public function RegisterForContest(Request $request)
    {
        $validatedData = $request->validate([
            'contest_id' => 'required|exists:contests,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $contest = Contest::findOrFail($validatedData['contest_id']);

        // Check if contest is still available for registration
        if (Carbon::parse($contest->date)->isPast()) {
            return response()->json([
                'message' => 'Contest registration is closed. Contest has already started.',
            ], 400);
        }

        // Check if student is already registered for this contest
        $existingParticipation = Participation::where('student_id', $student->id)
            ->where('contest_id', $contest->id)
            ->first();

        if ($existingParticipation) {
            return response()->json([
                'message' => 'You are already registered for this contest.',
                'participation' => $existingParticipation,
            ], 400);
        }

        // If classroom is provided, validate it's associated with the contest
        if (isset($validatedData['classroom_id'])) {
            $classroom = Classroom::findOrFail($validatedData['classroom_id']);
            $isClassroomAssociated = $contest->classrooms()
                ->where('classroom_id', $validatedData['classroom_id'])
                ->exists();

            if (!$isClassroomAssociated) {
                return response()->json([
                    'message' => 'Selected classroom is not associated with this contest.',
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $participation = Participation::create([
                'student_id' => $student->id,
                'contest_id' => $contest->id,
                'classroom_id' => $validatedData['classroom_id'] ?? null,
                'score' => null, // Will be calculated after submission
            ]);

            $participation->load(['student.user', 'contest', 'contest_classroom']);

            DB::commit();

            return response()->json([
                'message' => 'Successfully registered for the contest.',
                'participation' => $participation,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error registering for contest.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student's participations (ongoing and past)
     */
    public function GetMyParticipations(Request $request)
    {
        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $status = $request->input('status'); // 'upcoming', 'ongoing', 'completed'
        $perPage = $request->input('per_page', 10);

        $query = Participation::with(['contest', 'contest_classroom', 'participation_answers'])
            ->where('student_id', $student->id);

        // Filter by status if provided
        if ($status) {
            $now = Carbon::now();
            switch ($status) {
                case 'upcoming':
                    $query->whereHas('contest', function ($q) use ($now) {
                        $q->where('date', '>', $now);
                    });
                    break;
                case 'ongoing':
                    $query->whereHas('contest', function ($q) use ($now) {
                        $q->where('date', '<=', $now)
                          ->where(DB::raw('DATE_ADD(date, INTERVAL duration MINUTE)'), '>=', $now);
                    });
                    break;
                case 'completed':
                    $query->whereHas('contest', function ($q) use ($now) {
                        $q->where(DB::raw('DATE_ADD(date, INTERVAL duration MINUTE)'), '<', $now);
                    });
                    break;
            }
        }

        $participations = $query->orderBy('id', 'desc')
            ->paginate($perPage);

        // Add status and submission info to each participation
        $participations->getCollection()->each(function ($participation) {
            $participation->status = $this->getParticipationStatus($participation);
            $participation->has_submitted = $participation->participation_answers->isNotEmpty();
            $participation->submission_time = $participation->participation_answers->first()?->created_at;
        });

        return response()->json([
            'participations' => $participations,
        ], 200);
    }

    /**
     * Get specific participation details
     */
    public function GetParticipation($id)
    {
        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $participation = Participation::with([
            'contest', 
            'contest_classroom', 
            'participation_answers'
        ])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $participation->status = $this->getParticipationStatus($participation);
        $participation->has_submitted = $participation->participation_answers->isNotEmpty();
        $participation->can_submit = $this->canSubmitAnswers($participation);

        return response()->json([
            'participation' => $participation,
        ], 200);
    }

    /**
     * Submit answers for a contest
     */
    public function SubmitAnswers(Request $request, $participationId)
    {
        $validatedData = $request->validate([
            'answers' => 'required|string', // JSON string or encoded answers
        ]);

        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $participation = Participation::where('student_id', $student->id)
            ->findOrFail($participationId);

        // Check if submission is allowed
        if (!$this->canSubmitAnswers($participation)) {
            return response()->json([
                'message' => 'Submission is not allowed. Contest may have ended or not started yet.',
            ], 400);
        }

        // Check if answers were already submitted
        $existingAnswers = ParticipationAnswer::where('participation_id', $participation->id)
            ->first();

        if ($existingAnswers) {
            return response()->json([
                'message' => 'Answers have already been submitted for this contest.',
                'submission_time' => $existingAnswers->created_at,
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create participation answer
            $participationAnswer = ParticipationAnswer::create([
                'contest_id' => $participation->contest_id,
                'participation_id' => $participation->id,
                'answers' => $validatedData['answers'],
            ]);

            // TODO: Calculate score based on correct answers
            // For now, we'll set it to null and calculate later
            $participation->update(['score' => null]);

            DB::commit();

            return response()->json([
                'message' => 'Answers submitted successfully.',
                'participation' => $participation->load('participation_answers'),
                'submission_time' => $participationAnswer->created_at,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error submitting answers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel participation (only if contest hasn't started)
     */
    public function CancelParticipation($id)
    {
        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $participation = Participation::where('student_id', $student->id)
            ->findOrFail($id);

        $contest = $participation->contest;

        // Check if contest has started
        if (Carbon::parse($contest->date)->isPast()) {
            return response()->json([
                'message' => 'Cannot cancel participation. Contest has already started.',
            ], 400);
        }

        // Check if answers were already submitted
        $hasSubmitted = ParticipationAnswer::where('participation_id', $participation->id)
            ->exists();

        if ($hasSubmitted) {
            return response()->json([
                'message' => 'Cannot cancel participation. Answers have already been submitted.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $participation->delete();

            DB::commit();

            return response()->json([
                'message' => 'Participation canceled successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error canceling participation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get contest leaderboard (if contest has ended)
     */
    public function GetContestLeaderboard($contestId)
    {
        $contest = Contest::findOrFail($contestId);
        $now = Carbon::now();

        // Check if contest has ended
        $contestEndTime = Carbon::parse($contest->date)->addMinutes($contest->duration);
        if ($now->lt($contestEndTime)) {
            return response()->json([
                'message' => 'Leaderboard not available. Contest is still ongoing.',
                'contest_end_time' => $contestEndTime,
            ], 400);
        }

        // Get leaderboard
        $leaderboard = Participation::with(['student.user'])
            ->where('contest_id', $contestId)
            ->whereNotNull('score')
            ->orderBy('score', 'desc')
            ->get();

        // Add ranking
        $leaderboard->each(function ($participation, $index) {
            $participation->rank = $index + 1;
        });

        return response()->json([
            'contest' => $contest,
            'leaderboard' => $leaderboard,
            'total_participants' => $leaderboard->count(),
        ], 200);
    }

    /**
     * Get student's contest statistics
     */
    public function GetMyStats()
    {
        $user = Auth::user();
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user.',
            ], 404);
        }

        $now = Carbon::now();
        $participations = $student->participations()->with('contest')->get();

        $stats = [
            'total_participations' => $participations->count(),
            'completed_contests' => $participations->filter(function ($p) use ($now) {
                return Carbon::parse($p->contest->date)->addMinutes($p->contest->duration)->lt($now);
            })->count(),
            'upcoming_contests' => $participations->filter(function ($p) use ($now) {
                return Carbon::parse($p->contest->date)->gt($now);
            })->count(),
            'average_score' => $participations->whereNotNull('score')->avg('score'),
            'best_score' => $participations->whereNotNull('score')->max('score'),
            'total_score' => $participations->whereNotNull('score')->sum('score'),
        ];

        return response()->json([
            'student' => $student,
            'stats' => $stats,
        ], 200);
    }

    /**
     * Helper method to get contest status
     */
    private function getContestStatus($contest, $now = null)
    {
        $now = $now ?? Carbon::now();
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

    /**
     * Helper method to get participation status
     */
    private function getParticipationStatus($participation)
    {
        $contestStatus = $this->getContestStatus($participation->contest);
        $hasSubmitted = $participation->participation_answers->isNotEmpty();

        if ($contestStatus === 'upcoming') {
            return 'registered';
        } elseif ($contestStatus === 'ongoing') {
            return $hasSubmitted ? 'submitted' : 'in_progress';
        } else {
            return $hasSubmitted ? 'completed' : 'missed';
        }
    }

    /**
     * Helper method to get time until contest starts
     */
    private function getTimeUntilStart($contest, $now = null)
    {
        $now = $now ?? Carbon::now();
        $startTime = Carbon::parse($contest->date);

        if ($now->lt($startTime)) {
            return $startTime->diffForHumans($now);
        }

        return null;
    }

    /**
     * Helper method to check if answers can be submitted
     */
    private function canSubmitAnswers($participation)
    {
        $now = Carbon::now();
        $contest = $participation->contest;
        $startTime = Carbon::parse($contest->date);
        $endTime = $startTime->copy()->addMinutes($contest->duration);

        // Can only submit during the contest period
        return $now->between($startTime, $endTime);
    }
}
