<?php

namespace App\Http\Middleware;

use App\Enums\Roles;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminManagerAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        $admin = Roles::getRoleIdByValue(Roles::ADMIN->value);
        $manager = Roles::getRoleIdByValue(Roles::MGT->value);

        if ($user->employee->roleId === $admin || $user->employee->roleId === $manager) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
