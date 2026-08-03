<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class OrganizerController extends Controller
{
    public function loginForm(Request $request): Response|RedirectResponse
    {
        if ($request->session()->get('organizer')) {
            return redirect()->route('organizer.dashboard');
        }

        return Inertia::render('Organizer/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        $expected = config('wedding.organizer_password');

        if (! $expected || ! hash_equals((string) $expected, (string) $data['password'])) {
            return back()->withErrors(['password' => 'Nieprawidłowe hasło.']);
        }

        $request->session()->put('organizer', true);
        $request->session()->regenerate();

        return redirect()->route('organizer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('organizer');

        return redirect()->route('organizer.login');
    }

    public function dashboard(): Response
    {
        $photos = Photo::with(['guest:id,nickname', 'task:id,title,icon'])
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'url' => Storage::url($p->path),
                'thumb' => Storage::url($p->thumb_path),
                'guest' => $p->guest?->nickname,
                'task' => $p->task ? ['title' => $p->task->title, 'icon' => $p->task->icon] : null,
                'created_at' => $p->created_at->toIso8601String(),
            ]);

        $messages = Message::with('guest:id,nickname')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'guest' => $m->guest?->nickname,
                'photo_url' => $m->photo_path ? Storage::url($m->photo_path) : null,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return Inertia::render('Organizer/Dashboard', [
            'photos' => $photos,
            'messages' => $messages,
            'stats' => [
                'total_photos' => Photo::count(),
                'total_guests' => \App\Models\Guest::count(),
                'total_messages' => Message::count(),
            ],
        ]);
    }

    public function destroyMessage(Message $message): RedirectResponse
    {
        if ($message->photo_path) {
            Storage::disk('public')->delete([$message->photo_path, $message->thumb_path]);
        }
        $message->delete();

        return back();
    }

    public function destroyPhoto(Photo $photo): RedirectResponse
    {
        Storage::disk('public')->delete([$photo->path, $photo->thumb_path]);
        $photo->delete();

        return back();
    }

    public function downloadZip(): StreamedResponse
    {
        $filename = 'wesele-'.date('Y-m-d-His').'.zip';
        $tmp = tempnam(sys_get_temp_dir(), 'wedding_zip_');

        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);

        foreach (Photo::with('guest:id,nickname')->cursor() as $photo) {
            $abs = Storage::disk('public')->path($photo->path);
            if (! is_file($abs)) {
                continue;
            }
            $nick = preg_replace('/[^A-Za-z0-9_-]/', '_', $photo->guest?->nickname ?? 'gosc');
            $entryName = $nick.'/'.$photo->id.'-'.basename($photo->path);
            $zip->addFile($abs, $entryName);
        }
        $zip->close();

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $filename, ['Content-Type' => 'application/zip']);
    }
}
