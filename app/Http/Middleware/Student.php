<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Student
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        $user = Auth::user();
        
        // Check if user has student role
        if (!$this->isStudent($user)) {
            return response()->json([
                'message' => 'Unauthorized. Student access required.',
            ], 403);
        }

        // Check if user has a student profile
        if (!$user->students()->exists()) {
            return response()->json([
                'message' => 'Student profile not found. Please contact administrator.',
            ], 404);
        }

        return $next($request);
    }

    /**
     * Check if user has student role
     */
    private function isStudent($user): bool
    {
        // Check by role name (assuming 'student' or 'estudiante' is the role name)
        if ($user->role && in_array(strtolower($user->role->name), ['student', 'estudiante'])) {
            return true;
        }

        // Check by user_roles relationship
        return $user->roles()->whereIn('name', ['student', 'estudiante'])->exists();
    }
}
