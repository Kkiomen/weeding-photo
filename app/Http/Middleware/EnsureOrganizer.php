<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizer
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('organizer')) {
            return redirect()->route('organizer.login');
        }

        return $next($request);
    }
}
