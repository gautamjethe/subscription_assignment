<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FiftyPercentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        
        $allowRequest = rand(0, 1) === 1;

        if (!$allowRequest) {
            return response()->json(['message' => 'Request denied.'], 403);
        }

        return $next($request);
    }
}
