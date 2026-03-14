<?php

namespace App\Observers;

use App\Models\Manager;
use App\Models\Task;
use App\Models\TaskComment;
use App\Notifications\TaskActivityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TaskObserver
{
    public function created(Task $task): void
    {
        $this->notify($task, 'created', $this->currentActor());
    }

    public function updated(Task $task): void
    {
        $actor = $this->currentActor();

        if ($task->wasChanged('status')) {
            $this->notify($task, 'status_changed', $actor, [
                'old_status' => $task->getOriginal('status'),
                'new_status' => $task->status,
            ]);

            return;
        }

        if ($task->wasChanged(['title', 'description', 'priority', 'due_date', 'related_model_type', 'related_model_id'])) {
            $this->notify($task, 'updated', $actor);
        }
    }

    public function deleted(Task $task): void
    {
        $this->notify($task, 'deleted', $this->currentActor());
    }

    public static function commentCreated(TaskComment $comment, ?Manager $actor = null): void
    {
        $task = $comment->task;
        $task->loadMissing('assignees', 'creator');

        $notificationActor = $actor ?? $comment->manager;

        $excerpt = Str::limit(trim((string) $comment->body), 120);

        (new self())->notify($task, 'commented', $notificationActor, [
            'comment_id' => $comment->id,
            'excerpt' => $excerpt,
        ]);
    }

    public static function subtasksChanged(Task $task, ?Manager $actor = null, string $action = 'updated'): void
    {
        $task->loadMissing('assignees', 'creator');

        (new self())->notify($task, 'subtask_' . $action, $actor, []);
    }

    private function notify(Task $task, string $event, ?Manager $actor = null, array $extra = []): void
    {
        $task->loadMissing('assignees', 'creator');

        $recipients = collect();

        if ($task->assignees) {
            $recipients = $recipients->merge($task->assignees);
        }

        if ($task->creator) {
            $recipients->push($task->creator);
        }

        $superAdmins = Manager::role('Super-Admin')->get();
        if ($superAdmins->isNotEmpty()) {
            $recipients = $recipients->merge($superAdmins);
        }

        if ($actor) {
            $recipients = $recipients->reject(fn (Manager $recipient) => $recipient->id === $actor->id);
        }

        $recipients = $recipients->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new TaskActivityNotification($task, $event, $actor, $extra));
    }

    private function currentActor(): ?Manager
    {
        return Auth::guard('admin')->user();
    }
}
