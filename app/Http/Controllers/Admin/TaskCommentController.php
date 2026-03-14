<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Task;
use App\Models\TaskComment;
use App\Observers\TaskObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $this->ensureTaskVisibility($task, $manager);

        $canEditTasks = $manager->can('edit-tasks');
        $canComment = $manager->can('comment-on-tasks');

        if (!$canEditTasks && !$canComment) {
            abort(403);
        }

        if (!$canEditTasks && !$this->isTaskParticipant($task, $manager)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $comment = $task->comments()->create([
            'manager_id' => $manager->id,
            'body' => $validated['body'],
        ]);

        TaskObserver::commentCreated($comment, $manager);

        return back()->with('success', 'تم إضافة التعليق على المهمة.');
    }

    public function destroy(Request $request, Task $task, TaskComment $comment): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        if ($comment->task_id !== $task->id) {
            abort(404);
        }

        $this->ensureTaskVisibility($task, $manager);

        if ($manager->id !== $comment->manager_id && !$manager->can('delete-tasks')) {
            abort(403);
        }

        $comment->delete();

        return back()->with('status', 'تم حذف التعليق.');
    }

    private function ensureTaskVisibility(Task $task, Manager $manager): void
    {
        if (!$task->relationLoaded('assignees')) {
            $task->load('assignees');
        }

        if (!$task->isVisibleTo($manager)) {
            abort(403);
        }
    }

    private function isTaskParticipant(Task $task, Manager $manager): bool
    {
        if (!$task->relationLoaded('assignees')) {
            $task->load('assignees');
        }

        return $task->assignees->contains('id', $manager->id) || $task->creator_id === $manager->id;
    }
}
