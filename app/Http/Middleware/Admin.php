<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
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
        
        // Check if user has admin role
        if (!$this->isAdmin($user)) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has admin role
     */
    private function isAdmin($user): bool
    {
        // Check by role name (assuming 'admin' is the role name)
        if ($user->role && strtolower($user->role->name) === 'admin') {
            return true;
        }

        // Check by user_roles relationship
        return $user->roles()->where('name', 'admin')->exists();
    }
}
