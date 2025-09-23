<?php

namespace App\Http\Controllers\admin_panel;

use App\Models\Classroom;
use App\Models\Campus;
use App\Models\Contest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
   
    /**
     * Create a new classroom
     */
    public function CreateClassroom(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        try {
            // Create the classroom
            $classroom = Classroom::create($validatedData);

            // Load the campus relationship for response
            $classroom->load('campus');

            return response()->json([
                'message' => 'Classroom created successfully.',
                'classroom' => $classroom,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating classroom.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing classroom
     */
    public function UpdateClassroom(Request $request, $id)
    {
        // Find the classroom by ID
        $classroom = Classroom::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'campus_id' => 'sometimes|required|exists:campuses,id',
        ]);

        try {
            // Update the classroom with the validated data
            $classroom->update($validatedData);

            // Load the campus relationship for response
            $classroom->load('campus');

            return response()->json([
                'message' => 'Classroom updated successfully.',
                'classroom' => $classroom,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating classroom.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a classroom
     */
    public function DeleteClassroom($id)
    {
        // Find the classroom by ID
        $classroom = Classroom::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if classroom has associated contests
            $contestCount = $classroom->contests()->count();
            if ($contestCount > 0) {
                return response()->json([
                    'message' => 'Cannot delete classroom. It has associated contests.',
                    'associated_contests' => $contestCount,
                ], 400);
            }

            // Delete the classroom record
            $classroom->delete();

            DB::commit();

            return response()->json([
                'message' => 'Classroom deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error deleting classroom.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Force delete a classroom (removes all associations)
     */
    public function ForceDeleteClassroom($id)
    {
        // Find the classroom by ID
        $classroom = Classroom::findOrFail($id);

        try {
            DB::beginTransaction();

            // Remove all contest associations
            $classroom->contests()->detach();

            // Delete the classroom record
            $classroom->delete();

            DB::commit();

            return response()->json([
                'message' => 'Classroom force deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error force deleting classroom.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all classrooms with pagination
     */
    public function ListClassrooms(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Retrieve classrooms with relationships
        $classrooms = Classroom::with(['campus', 'contests'])
            ->orderBy('name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'classrooms' => $classrooms,
        ], 200);
    }

    /**
     * Get a specific classroom by ID
     */
    public function GetClassroom($id)
    {
        // Find the classroom by ID with relationships
        $classroom = Classroom::with(['campus', 'contests'])
            ->findOrFail($id);

        return response()->json([
            'classroom' => $classroom,
        ], 200);
    }

    /**
     * Search classrooms based on various criteria
     */
    public function SearchClassrooms(Request $request)
    {
        // Validate the search parameters
        $validatedData = $request->validate([
            'query' => 'nullable|string|max:255',
            'campus_id' => 'nullable|exists:campuses,id',
            'has_contests' => 'nullable|boolean',
        ]);

        $query = Classroom::with(['campus', 'contests']);

        // Search by name
        if (!empty($validatedData['query'])) {
            $searchQuery = $validatedData['query'];
            $query->where('name', 'like', '%' . $searchQuery . '%');
        }

        // Filter by campus
        if (!empty($validatedData['campus_id'])) {
            $query->where('campus_id', $validatedData['campus_id']);
        }

        // Filter by contest association
        if (isset($validatedData['has_contests'])) {
            if ($validatedData['has_contests']) {
                $query->whereHas('contests');
            } else {
                $query->whereDoesntHave('contests');
            }
        }

        $classrooms = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'classrooms' => $classrooms,
        ], 200);
    }

    /**
     * Get classrooms by campus
     */
    public function GetClassroomsByCampus($campusId)
    {
        // Validate campus exists
        $campus = Campus::findOrFail($campusId);

        // Get classrooms for the campus
        $classrooms = Classroom::with(['contests'])
            ->where('campus_id', $campusId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'campus' => $campus,
            'classrooms' => $classrooms,
        ], 200);
    }

    /**
     * Get classroom statistics
     */
    public function GetClassroomStats($id)
    {
        $classroom = Classroom::with(['campus', 'contests'])
            ->findOrFail($id);

        $stats = [
            'total_contests' => $classroom->contests->count(),
            'upcoming_contests' => $classroom->contests->where('date', '>', now())->count(),
            'past_contests' => $classroom->contests->where('date', '<', now())->count(),
            'campus_name' => $classroom->campus ? $classroom->campus->name : 'No campus assigned',
        ];

        return response()->json([
            'classroom' => $classroom,
            'stats' => $stats,
        ], 200);
    }

    /**
     * Get all classrooms grouped by campus
     */
    public function GetClassroomsGroupedByCampus()
    {
        $campuses = Campus::with(['classrooms' => function ($query) {
            $query->orderBy('name', 'asc');
        }])->orderBy('name', 'asc')->get();

        // Also get classrooms without campus
        $classroomsWithoutCampus = Classroom::whereNull('campus_id')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'campuses' => $campuses,
            'classrooms_without_campus' => $classroomsWithoutCampus,
        ], 200);
    }

    /**
     * Bulk create classrooms
     */
    public function BulkCreateClassrooms(Request $request)
    {
        $validatedData = $request->validate([
            'classrooms' => 'required|array|min:1',
            'classrooms.*.name' => 'required|string|max:255',
            'classrooms.*.campus_id' => 'required|exists:campuses,id',
        ]);

        try {
            DB::beginTransaction();

            $createdClassrooms = [];
            foreach ($validatedData['classrooms'] as $classroomData) {
                $classroom = Classroom::create($classroomData);
                $classroom->load('campus');
                $createdClassrooms[] = $classroom;
            }

            DB::commit();

            return response()->json([
                'message' => count($createdClassrooms) . ' classrooms created successfully.',
                'classrooms' => $createdClassrooms,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error creating classrooms.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update classrooms
     */
    public function BulkUpdateClassrooms(Request $request)
    {
        $validatedData = $request->validate([
            'classrooms' => 'required|array|min:1',
            'classrooms.*.id' => 'required|exists:classrooms,id',
            'classrooms.*.name' => 'sometimes|required|string|max:255',
            'classrooms.*.campus_id' => 'sometimes|required|exists:campuses,id',
        ]);

        try {
            DB::beginTransaction();

            $updatedClassrooms = [];
            foreach ($validatedData['classrooms'] as $classroomData) {
                $classroom = Classroom::findOrFail($classroomData['id']);
                $classroom->update($classroomData);
                $classroom->load('campus');
                $updatedClassrooms[] = $classroom;
            }

            DB::commit();

            return response()->json([
                'message' => count($updatedClassrooms) . ' classrooms updated successfully.',
                'classrooms' => $updatedClassrooms,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error updating classrooms.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete classrooms
     */
    public function BulkDeleteClassrooms(Request $request)
    {
        $validatedData = $request->validate([
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
            'force' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $classrooms = Classroom::whereIn('id', $validatedData['classroom_ids'])->get();
            $deletedCount = 0;
            $errors = [];

            foreach ($classrooms as $classroom) {
                $contestCount = $classroom->contests()->count();
                
                if ($contestCount > 0 && !($validatedData['force'] ?? false)) {
                    $errors[] = "Classroom '{$classroom->name}' has {$contestCount} associated contests";
                    continue;
                }

                // If force delete, remove associations
                if ($validatedData['force'] ?? false) {
                    $classroom->contests()->detach();
                }

                $classroom->delete();
                $deletedCount++;
            }

            DB::commit();

            $response = [
                'message' => "{$deletedCount} classrooms deleted successfully.",
                'deleted_count' => $deletedCount,
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error deleting classrooms.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer classrooms to another campus
     */
    public function TransferClassrooms(Request $request)
    {
        $validatedData = $request->validate([
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
            'new_campus_id' => 'required|exists:campuses,id',
        ]);

        try {
            DB::beginTransaction();

            $classrooms = Classroom::whereIn('id', $validatedData['classroom_ids'])->get();
            
            foreach ($classrooms as $classroom) {
                $classroom->update(['campus_id' => $validatedData['new_campus_id']]);
            }

            $classrooms->load('campus');

            DB::commit();

            return response()->json([
                'message' => count($classrooms) . ' classrooms transferred successfully.',
                'classrooms' => $classrooms,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error transferring classrooms.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available classrooms for a contest (not already assigned)
     */
    public function GetAvailableClassrooms(Request $request, $contestId = null)
    {
        $query = Classroom::with(['campus']);

        // If contest ID is provided, exclude classrooms already assigned to that contest
        if ($contestId) {
            $contest = Contest::findOrFail($contestId);
            $assignedClassroomIds = $contest->classrooms()->pluck('classroom_id')->toArray();
            $query->whereNotIn('id', $assignedClassroomIds);
        }

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->input('campus_id'));
        }

        $classrooms = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'classrooms' => $classrooms,
        ], 200);
    }
}
