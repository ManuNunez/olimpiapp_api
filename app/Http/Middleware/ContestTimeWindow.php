<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ContestTimeWindow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $timeWindow
     */
    public function handle(Request $request, Closure $next, string $timeWindow = 'during'): Response
    {
        // Get contest from request (should be added by ContestAccess middleware)
        $contest = $request->get('contest');
        
        if (!$contest) {
            return response()->json([
                'message' => 'Contest information not found. Please ensure proper middleware order.',
            ], 500);
        }

        $now = Carbon::now();
        $contestStart = Carbon::parse($contest->date);
        $contestEnd = $contestStart->copy()->addMinutes($contest->duration);

        $isAllowed = false;
        $message = '';

        switch ($timeWindow) {
            case 'before':
                // Action allowed only before contest starts
                $isAllowed = $now->lt($contestStart);
                $message = $isAllowed ? '' : 'This action is not allowed after the contest has started.';
                break;

            case 'during':
                // Action allowed only during contest
                $isAllowed = $now->between($contestStart, $contestEnd);
                if (!$isAllowed) {
                    if ($now->lt($contestStart)) {
                        $message = 'This action is not allowed before the contest starts.';
                    } else {
                        $message = 'This action is not allowed after the contest has ended.';
                    }
                }
                break;

            case 'after':
                // Action allowed only after contest ends
                $isAllowed = $now->gt($contestEnd);
                $message = $isAllowed ? '' : 'This action is not allowed until the contest has ended.';
                break;

            case 'not_during':
                // Action allowed before or after, but not during contest
                $isAllowed = !$now->between($contestStart, $contestEnd);
                $message = $isAllowed ? '' : 'This action is not allowed during the contest.';
                break;

            case 'before_or_during':
                // Action allowed before or during contest, but not after
                $isAllowed = $now->lte($contestEnd);
                $message = $isAllowed ? '' : 'This action is not allowed after the contest has ended.';
                break;

            default:
                return response()->json([
                    'message' => 'Invalid time window specified.',
                ], 500);
        }

        if (!$isAllowed) {
            return response()->json([
                'message' => $message,
                'contest_start' => $contestStart,
                'contest_end' => $contestEnd,
                'current_time' => $now,
            ], 403);
        }

        return $next($request);
    }
}
