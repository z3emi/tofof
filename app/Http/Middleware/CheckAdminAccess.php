<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if a user has the necessary permissions to access the admin panel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the currently authenticated user.
        $user = $request->user();

        // First, check if a user is logged in.
        // Then, check if the user either has the 'Super-Admin' role OR has the 'access-dashboard' permission.
if ($user && ($user->hasRole('Super-Admin') || $user->hasPermissionTo('view-admin-panel'))) {
    return $next($request);
}

        // If the user is not logged in or does not have the required role/permission,
        // deny access with a 403 Forbidden error.
        abort(403, 'UNAUTHORIZED ACTION.');
    }
}
