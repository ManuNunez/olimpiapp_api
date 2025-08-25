<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserRole;
use App\Models\User;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        if (!$this->IsStudent($user->id)) {
            return response()->json([
                'error' => 'Unauthorized', 
                'message' => 'Access restricted to students only'
            ], 403);
        }
        
        return $next($request);
    }

    private function IsStudent($userId): bool
    {
        // Check if the user has the 'student' role (role_id = 2)
        $studentRole = UserRole::where('user_id', $userId)
            ->where('role_id', 2)
            ->first();
            
        if ($studentRole) {
            return true;
        }
        return false;
    }
}