<?php

namespace App\Http\Middleware;

use App\Http\Controllers\GuestController;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $guest = GuestController::current($request);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'wedding' => [
                'couple' => config('wedding.couple'),
                'date' => config('wedding.date'),
                'venue' => config('wedding.venue'),
            ],
            'guest' => $guest ? [
                'id' => $guest->id,
                'nickname' => $guest->nickname,
                'xp' => $guest->xp,
                'level' => $guest->level,
            ] : null,
            'flash' => fn () => $request->session()->get('flash'),
        ];
    }
}
