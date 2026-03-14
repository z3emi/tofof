<?php

namespace App\Notifications;

use App\Models\Manager;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly string $event,
        private readonly ?Manager $actor = null,
        private readonly array $extra = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'icon' => $this->icon(),
            'message' => $this->message(),
            'url' => route('admin.tasks.board', ['focus' => $this->task->id]),
        ];
    }

    public function toWebPush(object $notifiable, ?object $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('تحديث المهام')
            ->body($this->message())
            ->icon(url('/favicon.ico'))
            ->action('open-task', 'عرض المهمة')
            ->data([
                'url' => route('admin.tasks.board', ['focus' => $this->task->id]),
            ]);
    }

    private function message(): string
    {
        $actorName = $this->actor?->name ?? 'أحد المدراء';
        $title = $this->task->title;

        return match ($this->event) {
            'created' => "قام {$actorName} بإنشاء مهمة جديدة بعنوان \"{$title}\".",
            'updated' => "قام {$actorName} بتحديث تفاصيل المهمة \"{$title}\".",
            'deleted' => "قام {$actorName} بحذف المهمة \"{$title}\".",
            'status_changed' => $this->statusChangedMessage($actorName, $title),
            'commented' => $this->commentedMessage($actorName, $title),
            'subtask_created' => "قام {$actorName} بإضافة مهمة فرعية للمهمة \"{$title}\".",
            'subtask_updated' => "قام {$actorName} بتحديث مهمة فرعية ضمن المهمة \"{$title}\".",
            'subtask_deleted' => "قام {$actorName} بحذف مهمة فرعية من المهمة \"{$title}\".",
            default => "هناك نشاط جديد على المهمة \"{$title}\".",
        };
    }

    private function statusChangedMessage(string $actorName, string $title): string
    {
        $newStatus = $this->extra['new_status'] ?? $this->task->status;
        $labels = Task::statusLabels();
        $statusLabel = $labels[$newStatus] ?? $newStatus;

        return "قام {$actorName} بتغيير حالة المهمة \"{$title}\" إلى {$statusLabel}.";
    }

    private function commentedMessage(string $actorName, string $title): string
    {
        $excerpt = $this->extra['excerpt'] ?? '';

        if ($excerpt !== '') {
            return "قام {$actorName} بالتعليق على المهمة \"{$title}\": \"{$excerpt}\"";
        }

        return "قام {$actorName} بإضافة تعليق جديد على المهمة \"{$title}\".";
    }

    private function icon(): string
    {
        return match ($this->event) {
            'created' => 'bi-plus-circle',
            'updated' => 'bi-pencil-square',
            'deleted' => 'bi-trash',
            'status_changed' => 'bi-kanban',
            'commented' => 'bi-chat-dots',
            'subtask_created', 'subtask_updated', 'subtask_deleted' => 'bi-list-check',
            default => 'bi-kanban',
        };
    }
}
