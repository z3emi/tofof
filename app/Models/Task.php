<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_TODO,
        self::STATUS_IN_PROGRESS,
        self::STATUS_REVIEW,
        self::STATUS_DONE,
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'creator_id',
        'related_model_type',
        'related_model_id',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected $with = ['assignees'];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_TODO => 'للعمل',
            self::STATUS_IN_PROGRESS => 'قيد التنفيذ',
            self::STATUS_REVIEW => 'للمراجعة',
            self::STATUS_DONE => 'مكتملة',
        ];
    }

    public static function priorityLabels(): array
    {
        return [
            self::PRIORITY_LOW => 'منخفضة',
            self::PRIORITY_MEDIUM => 'متوسطة',
            self::PRIORITY_HIGH => 'عالية',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'creator_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(Manager::class, 'manager_task')
            ->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(TaskSubtask::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo('related_model');
    }

    public function scopeForAssignee($query, Manager $manager)
    {
        return $query->whereHas('assignees', function ($q) use ($manager) {
            $q->where('manager_task.manager_id', $manager->id);
        });
    }

    public function scopeForAssignees($query, array $managerIds)
    {
        if (empty($managerIds)) {
            return $query;
        }

        return $query->whereHas('assignees', function ($q) use ($managerIds) {
            $q->whereIn('manager_task.manager_id', $managerIds);
        });
    }

    public function syncAssignees(array $managerIds): void
    {
        $ids = collect($managerIds)
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $this->assignees()->sync($ids);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_HIGH => 'danger',
            self::PRIORITY_MEDIUM => 'warning',
            default => 'secondary',
        };
    }

    public function getProgressAttribute(): ?array
    {
        $subtasks = $this->relationLoaded('subtasks') ? $this->subtasks : $this->subtasks()->get();

        if ($subtasks->isEmpty()) {
            return null;
        }

        $completed = $subtasks->where('is_completed', true)->count();
        $total = $subtasks->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    public function getIsCompletedAttribute(): bool
    {
        if ($this->status === self::STATUS_DONE) {
            return true;
        }

        return $this->completed_at !== null;
    }

    public function markCompletionTimestamp(): void
    {
        if ($this->status === self::STATUS_DONE) {
            $this->completed_at = $this->completed_at ?? now();
        } else {
            $this->completed_at = null;
        }
    }

    public function isVisibleTo(Manager $manager): bool
    {
        if ($manager->can('view-all-tasks')) {
            return true;
        }

        $assigneeIds = $this->assignees->pluck('id')->all();

        if ($manager->can('view-team-tasks')) {
            $teamIds = $manager->teamMemberIds();
            if (array_intersect($assigneeIds, $teamIds) || in_array($this->creator_id, $teamIds, true)) {
                return true;
            }
        }

        if ($manager->can('view-own-tasks')) {
            if (in_array($manager->id, $assigneeIds, true) || $this->creator_id === $manager->id) {
                return true;
            }
        }

        return false;
    }
}
