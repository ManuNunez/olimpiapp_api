<?php

namespace App\Http\Controllers;
use App\Models\Student;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use PhpParser\Builder\Class_;
use Symfony\Component\HttpFoundation\Response;

Class StudentController extends Controller{
    public function register_student(Request $request) : JsonResponse {
        $validated = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')],
            'school_id' => ['required', Rule::exists('schools', 'id')],
        ]);
        $student = Student::create([
            'user_id' => $validated['user_id'],
            'school_id' => $validated['school_id'],
            'code' => 'STU-' . strtoupper(uniqid()),
        ]);
        return response()->json([
            'message' => 'Student registered successfully.',
            'student' => $student,
        ], Response::HTTP_CREATED);
    }
    public function update_student(Request $request, $id) : JsonResponse {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'user_id' => ['sometimes', Rule::exists('users', 'id')],
            'school_id' => ['sometimes', Rule::exists('schools', 'id')],
        ]);
        if (isset($validated['user_id'])) {
            $student->user_id = $validated['user_id'];
        }
        if (isset($validated['school_id'])) {
            $student->school_id = $validated['school_id'];
        }
        $student->save();
        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $student,
        ], Response::HTTP_OK);
    }
    public function return_student($id) : JsonResponse {
        $student = Student::findOrFail($id);
        return response()->json([
            'student' => $student,
        ], Response::HTTP_OK);
    }
}