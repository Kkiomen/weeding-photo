<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Quote;
use App\Models\QuoteLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        return Inertia::render('Quotes', [
            'quotes' => $this->list($guest),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|min:3|max:200',
        ]);

        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        Quote::create([
            'guest_id' => $guest->id,
            'body' => trim($data['body']),
        ]);

        $guest->increment('xp', 15);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Cytat dodany +15 XP 🎙️',
        ]);
    }

    public function toggleLike(Request $request, Quote $quote): JsonResponse
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        $existing = QuoteLike::where('quote_id', $quote->id)
            ->where('guest_id', $guest->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $quote->decrement('likes_count');
            $liked = false;
        } else {
            QuoteLike::create(['quote_id' => $quote->id, 'guest_id' => $guest->id]);
            $quote->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $quote->fresh()->likes_count,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        return response()->json(['quotes' => $this->list($guest)]);
    }

    private function list(Guest $guest): array
    {
        $likedIds = QuoteLike::where('guest_id', $guest->id)->pluck('quote_id')->all();

        return Quote::orderByDesc('likes_count')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'body' => $q->body,
                'likes_count' => $q->likes_count,
                'liked' => in_array($q->id, $likedIds, true),
                'mine' => $q->guest_id === $guest->id,
            ])
            ->toArray();
    }
}
