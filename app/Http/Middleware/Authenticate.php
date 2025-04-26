<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{

    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }

        return $request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*')) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'No autenticado',
                'errors' => [
                    'token' => ['Token no valido']
                ]
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
