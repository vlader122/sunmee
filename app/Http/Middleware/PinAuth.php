<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PinAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('pin_verified')) {
            return redirect()->route('pin.show')->with('intended', $request->url());
        }

        return $next($request);
    }
}
