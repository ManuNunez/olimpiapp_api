<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contest;
use App\Models\Participation;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ContestAccess
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
        $student = $user->students()->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        // Get contest ID from route parameter
        $contestId = $request->route('contest_id') ?? $request->route('contestId');
        
        if (!$contestId) {
            return response()->json([
                'message' => 'Contest ID is required.',
            ], 400);
        }

        // Check if contest exists
        $contest = Contest::find($contestId);
        if (!$contest) {
            return response()->json([
                'message' => 'Contest not found.',
            ], 404);
        }

        // Check if student has participation in this contest
        $participation = Participation::where('student_id', $student->id)
            ->where('contest_id', $contestId)
            ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'You are not registered for this contest.',
            ], 403);
        }

        // Add contest and participation to request for controller use
        $request->merge([
            'contest' => $contest,
            'participation' => $participation,
            'student' => $student,
        ]);

        return $next($request);
    }
}
