<?php

namespace App\Http\Middleware;

use App\Http\Controllers\GuestController;
use Closure;
use Illuminate\Http\Request;

class EnsureGuest
{
    public function handle(Request $request, Closure $next)
    {
        $guest = GuestController::current($request);

        if (! $guest) {
            return redirect()->route('home');
        }

        $request->attributes->set('guest', $guest);

        return $next($request);
    }
}
