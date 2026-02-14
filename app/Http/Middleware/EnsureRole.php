<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Support\Http\ApiResponder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function __construct(private readonly ApiResponder $responder)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->responder->error('Unauthorized', 401);
        }

        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $role) {
                $role = trim($role);
                if ($role !== '') {
                    $allowedRoles[] = $role;
                }
            }
        }

        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if (! in_array($userRole, $allowedRoles, true)) {
            return $this->responder->error('Forbidden', 403);
        }

        return $next($request);
    }
}
