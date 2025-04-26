<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $store = $request->route('store');

        if ($store->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para esta acción'
            ], 403);
        }

        return $next($request);
    }
}
