<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            // Instead of redirecting, return a JSON response for unauthorized access
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
    }
}
