<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $allowed = explode('|', $roles);
        $userRole = optional($request->user()->role)->name;

        if (!in_array($userRole, $allowed)) {
            return response()->json(['message' => 'Forbidden: role tidak mencukupi'], 403);
        }

        return $next($request);
    }
}
