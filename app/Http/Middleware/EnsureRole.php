<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthorized');
        }

        $allowedRoles = [];
        foreach (explode(',', $roles) as $role) {
            $role = trim($role);
            if ($role !== '') {
                $allowedRoles[] = $role;
            }
        }

        if (! in_array((string) $user->role, $allowedRoles, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
