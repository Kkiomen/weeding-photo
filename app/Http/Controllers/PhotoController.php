<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Photo;
use App\Models\Task;
use App\Services\PhotoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PhotoController extends Controller
{
    public function __construct(private PhotoStorage $storage) {}

    public function upload(Request $request, ?Task $task = null): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|max:15360',
        ]);

        /** @var Guest $guest */
        $guest = $request->attributes->get('guest');
        $file = $request->file('photo');

        $hash = hash_file('sha256', $file->getRealPath());

        $existing = Photo::where('guest_id', $guest->id)
            ->where('file_hash', $hash)
            ->first();

        if ($existing) {
            return back()->with('flash', ['type' => 'info', 'message' => 'To zdjęcie już wysłałeś.']);
        }

        $stored = $this->storage->store($file);

        Photo::create([
            'guest_id' => $guest->id,
            'task_id' => $task?->id,
            'path' => $stored['path'],
            'thumb_path' => $stored['thumb_path'],
            'file_hash' => $stored['hash'],
        ]);

        $reward = $task?->xp_reward ?? 5;
        $guest->increment('xp', $reward);

        return back()->with('flash', [
            'type' => 'success',
            'message' => "+{$reward} XP!",
        ]);
    }

    public function gallery(Request $request): Response
    {
        $sort = $request->query('sort') === 'random' ? 'random' : 'newest';

        $query = Photo::with(['guest:id,nickname', 'task:id,title,icon', 'bingoMark.field:id,title,icon']);

        $query = $sort === 'random'
            ? $query->inRandomOrder()
            : $query->orderByDesc('id');

        $photos = $query->limit(200)->get()->map(fn ($p) => $this->transform($p));

        return Inertia::render('Gallery', [
            'photos' => $photos,
            'total_photos' => Photo::count(),
            'sort' => $sort,
            'guest' => $this->guestPayload($request),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $since = (int) $request->query('since', 0);

        $photos = Photo::with(['guest:id,nickname', 'task:id,title,icon', 'bingoMark.field:id,title,icon'])
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn ($p) => $this->transform($p));

        return response()->json([
            'photos' => $photos,
            'latest_id' => Photo::max('id') ?? 0,
        ]);
    }

    private function transform(Photo $p): array
    {
        $bingoField = $p->bingoMark?->field;

        return [
            'id' => $p->id,
            'url' => Storage::url($p->path),
            'thumb' => Storage::url($p->thumb_path),
            'guest' => $p->guest?->nickname,
            'task' => $p->task ? ['title' => $p->task->title, 'icon' => $p->task->icon] : null,
            'bingo' => $bingoField ? ['title' => $bingoField->title, 'icon' => $bingoField->icon] : null,
            'created_at' => $p->created_at->toIso8601String(),
        ];
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
