<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->role !== 'seller') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo vendedores pueden realizar esta acción'
            ], 403);
        }

        return $next($request);
    }
}
