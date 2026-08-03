<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reward;
use App\Models\RewardClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScratchController extends Controller
{
    private const COOLDOWN_MINUTES = 30;

    public function index(Request $request): Response
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        return Inertia::render('Scratch', [
            'cooldown_seconds' => $this->cooldownSeconds($guest),
            'history' => $this->history($guest),
        ]);
    }

    public function scratch(Request $request): JsonResponse
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        $remaining = $this->cooldownSeconds($guest);

        if ($remaining > 0) {
            return response()->json([
                'error' => 'cooldown',
                'cooldown_seconds' => $remaining,
            ], 429);
        }

        $reward = $this->pickWeighted();

        if (! $reward) {
            return response()->json(['error' => 'no_rewards'], 500);
        }

        RewardClaim::create([
            'guest_id' => $guest->id,
            'reward_id' => $reward->id,
            'claimed_at' => now(),
        ]);

        if ($reward->xp_bonus > 0) {
            $guest->increment('xp', $reward->xp_bonus);
        }

        return response()->json([
            'reward' => [
                'id' => $reward->id,
                'title' => $reward->title,
                'description' => $reward->description,
                'icon' => $reward->icon,
                'xp_bonus' => $reward->xp_bonus,
            ],
            'cooldown_seconds' => self::COOLDOWN_MINUTES * 60,
            'history' => $this->history($guest),
        ]);
    }

    private function cooldownSeconds(Guest $guest): int
    {
        $last = RewardClaim::where('guest_id', $guest->id)
            ->orderByDesc('claimed_at')
            ->first();

        if (! $last) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($last->claimed_at);
        $cooldown = self::COOLDOWN_MINUTES * 60;
        $remaining = $cooldown - abs($elapsed);

        return max(0, (int) $remaining);
    }

    private function pickWeighted(): ?Reward
    {
        $rewards = Reward::all();
        if ($rewards->isEmpty()) {
            return null;
        }

        $total = $rewards->sum('weight');
        if ($total <= 0) {
            return $rewards->random();
        }

        $roll = random_int(1, $total);
        $acc = 0;
        foreach ($rewards as $r) {
            $acc += $r->weight;
            if ($roll <= $acc) {
                return $r;
            }
        }

        return $rewards->last();
    }

    private function history(Guest $guest): array
    {
        return RewardClaim::with('reward')
            ->where('guest_id', $guest->id)
            ->orderByDesc('claimed_at')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'icon' => $c->reward->icon,
                'title' => $c->reward->title,
                'xp_bonus' => $c->reward->xp_bonus,
                'claimed_at' => $c->claimed_at->toIso8601String(),
            ])
            ->toArray();
    }
}
