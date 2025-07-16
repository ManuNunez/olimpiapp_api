<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Participation;
use Symfony\Component\HttpFoundation\Response;

class ResourceOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $resourceType
     */
    public function handle(Request $request, Closure $next, string $resourceType = 'participation'): Response
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

        // Check ownership based on resource type
        switch ($resourceType) {
            case 'participation':
                return $this->checkParticipationOwnership($request, $next, $student);
            
            default:
                return response()->json([
                    'message' => 'Invalid resource type specified.',
                ], 500);
        }
    }

    /**
     * Check if student owns the participation resource
     */
    private function checkParticipationOwnership(Request $request, Closure $next, $student): Response
    {
        // Get participation ID from route parameter
        $participationId = $request->route('participation_id') ?? 
                          $request->route('participationId') ?? 
                          $request->route('id');
        
        if (!$participationId) {
            return response()->json([
                'message' => 'Participation ID is required.',
            ], 400);
        }

        // Check if participation exists and belongs to the student
        $participation = Participation::where('id', $participationId)
            ->where('student_id', $student->id)
            ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'Participation not found or you do not have access to this resource.',
            ], 404);
        }

        // Add participation to request for controller use
        $request->merge([
            'participation' => $participation,
            'student' => $student,
        ]);

        return $next($request);
    }
}
