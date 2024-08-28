<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated and if their role matches the required role
        if (!Auth::check() || Auth::user()->role->name !== $role) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // If the role matches, proceed with the request
        return $next($request);
    }
}
