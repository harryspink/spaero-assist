<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware if user is not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // If the route is already teams.index or teams.create, proceed
        if ($request->routeIs('teams.index') || $request->routeIs('teams.create')) {
            return $next($request);
        }

        // Check if user has any teams
        if ($user->allTeams() === []) {
            // If user has no teams, redirect to create organisation page
            return redirect()->route('teams.create')->with('message', 'Please create an organisation to continue.');
        }

        // Check if user has a current team selected
        if (!$user->currentTeam) {
            // If user has teams but no current organisation selected, redirect to organisations index
            return redirect()->route('teams.index')->with('message', 'Please select an organisation to continue.');
        }

        return $next($request);
    }
}
