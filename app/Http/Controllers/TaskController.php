<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function random(Request $request): JsonResponse
    {
        $guest = $request->attributes->get('guest');

        $doneIds = $guest->photos()->whereNotNull('task_id')->pluck('task_id');

        $pool = Task::whereNotIn('id', $doneIds);
        if ($pool->count() === 0) {
            $pool = Task::query();
        }

        $task = $pool->inRandomOrder()->first();

        return response()->json([
            'task' => $task ? [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'icon' => $task->icon,
                'xp_reward' => $task->xp_reward,
            ] : null,
        ]);
    }

    public function index(Request $request): Response
    {
        $guest = $request->attributes->get('guest');

        $completedTaskIds = $guest->photos()
            ->whereNotNull('task_id')
            ->pluck('task_id')
            ->unique()
            ->values();

        $tasks = Task::orderBy('sort_order')->get()->map(fn ($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'icon' => $t->icon,
            'xp_reward' => $t->xp_reward,
            'completed' => $completedTaskIds->contains($t->id),
        ]);

        return Inertia::render('Tasks', [
            'tasks' => $tasks,
            'guest' => [
                'id' => $guest->id,
                'nickname' => $guest->nickname,
                'xp' => $guest->xp,
                'level' => $guest->level,
            ],
        ]);
    }
}
