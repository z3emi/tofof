<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Task;
use App\Models\TaskSubtask;
use App\Observers\TaskObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskSubtaskController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $this->ensureTaskVisibility($task, $manager);

        if (!$manager->can('edit-tasks') && !$this->isTaskParticipant($task, $manager)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $task->subtasks()->create([
            'title' => $validated['title'],
        ]);

        TaskObserver::subtasksChanged($task, $manager, 'created');

        return back()->with('success', 'تم إضافة مهمة فرعية جديدة.');
    }

    public function update(Request $request, Task $task, TaskSubtask $subtask): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        if ($subtask->task_id !== $task->id) {
            abort(404);
        }

        $this->ensureTaskVisibility($task, $manager);

        if (!$manager->can('edit-tasks') && !$this->isTaskParticipant($task, $manager)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        $subtask->fill($validated);
        $subtask->save();

        TaskObserver::subtasksChanged($task, $manager, 'updated');

        return back()->with('success', 'تم تحديث المهمة الفرعية.');
    }

    public function destroy(Request $request, Task $task, TaskSubtask $subtask): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        if ($subtask->task_id !== $task->id) {
            abort(404);
        }

        $this->ensureTaskVisibility($task, $manager);

        if (!$manager->can('edit-tasks') && !$manager->can('delete-tasks') && !$this->isTaskParticipant($task, $manager)) {
            abort(403);
        }

        $subtask->delete();

        TaskObserver::subtasksChanged($task, $manager, 'deleted');

        return back()->with('status', 'تم حذف المهمة الفرعية.');
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
