<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SlideshowController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Slideshow');
    }

    public function feed(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 60);

        $photos = Photo::with(['guest:id,nickname', 'task:id,title,icon'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'type' => 'photo',
                'id' => 'photo-'.$p->id,
                'url' => Storage::url($p->path),
                'guest' => $p->guest?->nickname,
                'task' => $p->task ? ['title' => $p->task->title, 'icon' => $p->task->icon] : null,
            ])
            ->values();

        $messages = Message::with('guest:id,nickname')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn ($m) => [
                'type' => 'message',
                'id' => 'msg-'.$m->id,
                'body' => $m->body,
                'guest' => $m->guest?->nickname,
                'photo_url' => $m->photo_path ? Storage::url($m->photo_path) : null,
            ])
            ->values();

        // Interleave: co ~5 zdjęć wtrącamy wiadomość.
        $slides = [];
        $messagesArr = $messages->all();
        $mIndex = 0;
        foreach ($photos as $i => $photo) {
            $slides[] = $photo;
            if (($i + 1) % 5 === 0 && $mIndex < count($messagesArr)) {
                $slides[] = $messagesArr[$mIndex++];
            }
        }
        // Doklej resztę wiadomości na koniec (jeśli zdjęć mało)
        while ($mIndex < count($messagesArr)) {
            $slides[] = $messagesArr[$mIndex++];
        }

        return response()->json(['slides' => $slides]);
    }
}
