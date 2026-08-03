<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Ranking', [
            'ranking' => $this->build(),
            'guest' => $this->guestPayload($request),
        ]);
    }

    public function feed(): JsonResponse
    {
        return response()->json(['ranking' => $this->build()]);
    }

    private function build(): array
    {
        return Guest::query()
            ->withCount('photos')
            ->orderByDesc('xp')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn ($g, $i) => [
                'rank' => $i + 1,
                'id' => $g->id,
                'nickname' => $g->nickname,
                'xp' => $g->xp,
                'level' => (int) floor($g->xp / 100) + 1,
                'photos' => $g->photos_count,
            ])
            ->toArray();
    }

    private function guestPayload(Request $request): array
    {
        $g = $request->attributes->get('guest');

        return [
            'id' => $g->id,
            'nickname' => $g->nickname,
            'xp' => $g->xp,
            'level' => $g->level,
        ];
    }
}
