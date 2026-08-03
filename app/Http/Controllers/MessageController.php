<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Message;
use App\Services\PhotoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function __construct(private PhotoStorage $storage) {}

    public function create(Request $request): Response
    {
        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        return Inertia::render('Message', [
            'messages' => $this->list($guest),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|min:3|max:500',
            'photo' => 'nullable|image|max:15360',
        ]);

        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');

        $photoPath = null;
        $thumbPath = null;

        if ($request->hasFile('photo')) {
            $stored = $this->storage->store($request->file('photo'), 'messages');
            $photoPath = $stored['path'];
            $thumbPath = $stored['thumb_path'];
        }

        Message::create([
            'guest_id' => $guest->id,
            'body' => trim($data['body']),
            'photo_path' => $photoPath,
            'thumb_path' => $thumbPath,
        ]);

        $guest->increment('xp', 20);

        return redirect()->route('message.create')->with('flash', [
            'type' => 'success',
            'message' => 'Wiadomość doręczona +20 XP 💌',
        ]);
    }

    private function list(Guest $guest): array
    {
        return Message::with('guest:id,nickname')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'guest' => $m->guest?->nickname,
                'photo_url' => $m->photo_path ? \Illuminate\Support\Facades\Storage::url($m->photo_path) : null,
                'thumb_url' => $m->thumb_path ? \Illuminate\Support\Facades\Storage::url($m->thumb_path) : null,
                'mine' => $m->guest_id === $guest->id,
                'created_at' => $m->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    public function slideshowFeed(): JsonResponse
    {
        $messages = Message::with('guest:id,nickname')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'guest' => $m->guest?->nickname,
                'photo_url' => $m->photo_path ? Storage::url($m->photo_path) : null,
            ]);

        return response()->json(['messages' => $messages]);
    }
}
