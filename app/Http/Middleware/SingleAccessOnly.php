<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\Roles;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SingleAccessOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userRoleId = auth()->user()->employee->roleId;
        $roleId = Roles::getRoleIdByValue($role);
        
        if ($userRoleId === $roleId) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized,'. ucwords($role).' access only'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
