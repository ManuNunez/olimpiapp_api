<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        $user = Auth::user();
        
        // Check if user has any of the required roles
        if (!$this->hasAnyRole($user, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has any of the specified roles
     */
    private function hasAnyRole($user, array $roles): bool
    {
        // Normalize roles to lowercase for comparison
        $roles = array_map('strtolower', $roles);
        
        // Check by direct role relationship
        if ($user->role && in_array(strtolower($user->role->name), $roles)) {
            return true;
        }

        // Check by user_roles relationship
        return $user->roles()->whereIn('name', $roles)->exists();
    }
}
