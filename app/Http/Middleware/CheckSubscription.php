<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $firm = $user->firm;
        if (!$firm || !$firm->expire_at || $firm->expire_at->isPast()) {
            return response()->json([
                'message' => 'Your subscription has expired or is not active. Please purchase a plan to continue.',
                'subscription_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
