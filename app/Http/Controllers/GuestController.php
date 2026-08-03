<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    public function splash(Request $request): Response|RedirectResponse
    {
        if ($guest = self::current($request)) {
            return redirect()->route('tasks.index');
        }

        return Inertia::render('Splash');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nickname' => 'required|string|min:2|max:40',
        ]);

        $guest = Guest::create(['nickname' => trim($data['nickname'])]);
        $request->session()->put('guest_id', $guest->id);

        return redirect()->route('tasks.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('guest_id');

        return redirect()->route('home');
    }

    public static function current(Request $request): ?Guest
    {
        $id = $request->session()->get('guest_id');
        if (! $id) {
            return null;
        }

        return Guest::find($id);
    }
}
