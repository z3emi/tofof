<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\Order;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':create-tasks', ['only' => ['store']]);
        $this->middleware($permissionMiddleware . ':edit-tasks', ['only' => ['update']]);
        $this->middleware($permissionMiddleware . ':delete-tasks', ['only' => ['destroy']]);
        $this->middleware($permissionMiddleware . ':manage-task-status', ['only' => ['updateStatus']]);
    }

    public function board(Request $request)
    {
        /** @var Manager|null $manager */
        $manager = $request->user('admin');

        if (!$manager) {
            abort(403);
        }

        $viewPermissions = collect([
            'view-own-tasks',
            'view-team-tasks',
            'view-all-tasks',
        ]);

        if (!$viewPermissions->contains(fn (string $perm) => $manager->can($perm))) {
            abort(403);
        }

        $scopeOptions = [];
        if ($manager->can('view-own-tasks')) {
            $scopeOptions['own'] = 'مهامي';
        }
        if ($manager->can('view-team-tasks')) {
            $scopeOptions['team'] = 'مهام فريقي';
        }
        if ($manager->can('view-all-tasks')) {
            $scopeOptions['all'] = 'كل المهام';
        }

        $requestedScope = $request->input('scope');
        $activeScope = $this->resolveScope($manager, $requestedScope);

        $tasksQuery = Task::query()
            ->with(['creator', 'assignees', 'subtasks', 'relatedModel', 'comments.manager'])
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->input('priority'));
            })
            ->when($request->filled('assignee_id'), function ($query) use ($request) {
                $assigneeId = (int) $request->input('assignee_id');
                $query->whereHas('assignees', fn($q) => $q->where('manager_task.manager_id', $assigneeId));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = trim((string) $request->input('q'));
                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('id', $search);
                    });
                }
            });

        $tasksQuery = $this->applyScopeFilter($tasksQuery, $manager, $activeScope);

        $tasks = $tasksQuery
            ->orderByRaw("FIELD(status, '" . implode("','", Task::STATUSES) . "')")
            ->orderByRaw("FIELD(priority, 'high','medium','low')")
            ->orderBy('due_date', 'asc')
            ->orderByDesc('updated_at')
            ->get();

        $tasksByStatus = $tasks->groupBy('status');

        $assigneeOptions = Manager::query()
            ->orderBy('name')
            ->get(['id', 'name', 'avatar']);

        $statusLabels = Task::statusLabels();
        $priorityLabels = Task::priorityLabels();

        $canManageStatus = $manager->can('manage-task-status');
        $canEditTasks = $manager->can('edit-tasks');
        $canDeleteTasks = $manager->can('delete-tasks');
        $canAssignTasks = $manager->can('assign-tasks');
        $canEditCreator = $manager->can('edit-task-creator');
        $canViewDetails = $manager->can('view-task-details') || $canEditTasks;
        $canCommentOnTasks = $manager->can('comment-on-tasks') || $canEditTasks;
        $canReopenTasks = $manager->can('reopen-completed-tasks');

        $relatedTypeOptions = [
            '' => 'غير مرتبط',
            Customer::class => 'عميل',
            Order::class => 'طلب',
        ];

        $relatedLookupRoutes = [
            'customer' => route('admin.tasks.lookups.customers'),
            'order' => route('admin.tasks.lookups.orders'),
        ];

        $focusTaskId = $request->integer('focus');

        return view('admin.tasks.board', [
            'statusLabels' => $statusLabels,
            'priorityLabels' => $priorityLabels,
            'tasksByStatus' => $tasksByStatus,
            'assigneeOptions' => $assigneeOptions,
            'scopeOptions' => $scopeOptions,
            'activeScope' => $activeScope,
            'canManageStatus' => $canManageStatus,
            'canEditTasks' => $canEditTasks,
            'canDeleteTasks' => $canDeleteTasks,
            'canAssignTasks' => $canAssignTasks,
            'canEditCreator' => $canEditCreator,
            'relatedTypeOptions' => $relatedTypeOptions,
            'relatedLookupRoutes' => $relatedLookupRoutes,
            'focusTaskId' => $focusTaskId,
            'currentManager' => $manager,
            'canViewDetails' => $canViewDetails,
            'canCommentOnTasks' => $canCommentOnTasks,
            'canReopenTasks' => $canReopenTasks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $data = $this->validateTaskData($request, $manager);

        $task = null;

        DB::transaction(function () use ($data, $manager, &$task) {
            $task = new Task();
            $task->fill(Arr::except($data, ['assignees', 'related_type', 'related_id']));
            $task->creator_id = $data['creator_id'] ?? $manager->id;

            if (!empty($data['related_type']) && !empty($data['related_id'])) {
                $task->related_model_type = $data['related_type'];
                $task->related_model_id = $data['related_id'];
            }

            $task->markCompletionTimestamp();
            $task->save();

            $task->syncAssignees($data['assignees']);
        });

        return redirect()->route('admin.tasks.board', ['scope' => $this->resolveScope($manager, null), 'focus' => $task?->id])
            ->with('success', 'تم إنشاء المهمة بنجاح.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $this->ensureTaskVisibility($task, $manager);

        $originalStatus = $task->status;
        $data = $this->validateTaskData($request, $manager, $task);

        DB::transaction(function () use ($task, $data, $manager, $originalStatus) {
            $task->fill(Arr::except($data, ['assignees', 'related_type', 'related_id']));

            if ($manager->can('edit-task-creator') && isset($data['creator_id'])) {
                $task->creator_id = $data['creator_id'];
            }

            if (!empty($data['related_type']) && !empty($data['related_id'])) {
                $task->related_model_type = $data['related_type'];
                $task->related_model_id = $data['related_id'];
            } else {
                $task->related_model_type = null;
                $task->related_model_id = null;
            }

            if ($originalStatus === Task::STATUS_DONE && $task->status !== Task::STATUS_DONE && !$manager->can('reopen-completed-tasks')) {
                throw ValidationException::withMessages([
                    'status' => 'لا تملك صلاحية إعادة فتح المهام المكتملة.',
                ]);
            }

            $task->markCompletionTimestamp();
            $task->save();

            if (isset($data['assignees'])) {
                $task->syncAssignees($data['assignees']);
            }
        });

        return redirect()->route('admin.tasks.board', ['scope' => $this->resolveScope($manager, null), 'focus' => $task->id])
            ->with('success', 'تم تحديث المهمة بنجاح.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $this->ensureTaskVisibility($task, $manager);

        $task->delete();

        return redirect()->route('admin.tasks.board', ['scope' => $this->resolveScope($manager, null)])
            ->with('status', 'تم حذف المهمة.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        /** @var Manager $manager */
        $manager = $request->user('admin');

        $this->ensureTaskVisibility($task, $manager);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Task::STATUSES)],
        ]);

        if (!$manager->can('manage-task-status')) {
            abort(403);
        }

        $newStatus = $validated['status'];

        if ($task->status === Task::STATUS_DONE && $newStatus !== Task::STATUS_DONE && !$manager->can('reopen-completed-tasks')) {
            return response()->json([
                'success' => false,
                'message' => 'لا تملك صلاحية إعادة فتح المهام المكتملة.',
            ], 403);
        }

        $task->status = $newStatus;
        $task->markCompletionTimestamp();
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة المهمة.',
        ]);
    }

    private function resolveScope(Manager $manager, ?string $requested): string
    {
        if ($requested === 'all' && $manager->can('view-all-tasks')) {
            return 'all';
        }

        if ($requested === 'team' && $manager->can('view-team-tasks')) {
            return 'team';
        }

        if ($requested === 'own' && $manager->can('view-own-tasks')) {
            return 'own';
        }

        if ($manager->can('view-all-tasks')) {
            return 'all';
        }

        if ($manager->can('view-team-tasks')) {
            return 'team';
        }

        return 'own';
    }

    private function applyScopeFilter($query, Manager $manager, string $scope)
    {
        if ($scope === 'all' && $manager->can('view-all-tasks')) {
            return $query;
        }

        return $query->where(function ($q) use ($manager, $scope) {
            if ($scope === 'team' && $manager->can('view-team-tasks')) {
                $teamIds = $manager->teamMemberIds();
                $q->where(function ($teamQuery) use ($teamIds) {
                    $teamQuery->whereHas('assignees', function ($assigneesQuery) use ($teamIds) {
                        $assigneesQuery->whereIn('manager_task.manager_id', $teamIds);
                    })->orWhereIn('creator_id', $teamIds);
                });

                return;
            }

            $q->where(function ($ownQuery) use ($manager) {
                $ownQuery->whereHas('assignees', function ($assigneesQuery) use ($manager) {
                    $assigneesQuery->where('manager_task.manager_id', $manager->id);
                })->orWhere('creator_id', $manager->id);
            });
        });
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

    private function validateTaskData(Request $request, Manager $manager, ?Task $task = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'status' => ['required', Rule::in(Task::STATUSES)],
            'due_date' => ['nullable', 'date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['integer', 'exists:managers,id'],
            'related_type' => ['nullable', 'string'],
            'related_id' => ['nullable', 'integer'],
        ];

        if ($manager->can('edit-task-creator')) {
            $rules['creator_id'] = ['required', 'integer', 'exists:managers,id'];
        }

        $data = $request->validate($rules);

        if ($data['related_type'] ?? null) {
            $allowedRelated = [Customer::class, Order::class];
            if (!in_array($data['related_type'], $allowedRelated, true)) {
                throw ValidationException::withMessages([
                    'related_type' => 'نوع الربط المحدد غير مدعوم.',
                ]);
            }

            if (empty($data['related_id'])) {
                throw ValidationException::withMessages([
                    'related_id' => 'يرجى اختيار السجل المرتبط.',
                ]);
            }

            $relatedId = (int) $data['related_id'];
            $exists = match ($data['related_type']) {
                Customer::class => Customer::query()->whereKey($relatedId)->exists(),
                Order::class => Order::query()->whereKey($relatedId)->exists(),
            };

            if (!$exists) {
                throw ValidationException::withMessages([
                    'related_id' => 'السجل المرتبط غير موجود.',
                ]);
            }

            $data['related_id'] = $relatedId;
        } else {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        if ($task && !$task->relationLoaded('assignees')) {
            $task->load('assignees');
        }

        $assigneeIds = collect($data['assignees'] ?? []);
        if ($assigneeIds->isEmpty() && !$task) {
            $assigneeIds = collect([$manager->id]);
        }

        if (!$manager->can('assign-tasks')) {
            if ($task) {
                $assigneeIds = collect($task->assignees->pluck('id')->all());
            } else {
                $assigneeIds = collect([$manager->id]);
            }
        } elseif ($assigneeIds->isEmpty()) {
            $assigneeIds = collect([$manager->id]);
        }

        $data['assignees'] = $assigneeIds->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (!$manager->can('edit-task-creator')) {
            $data['creator_id'] = $task?->creator_id ?? $manager->id;
        }

        if (!empty($data['related_type'])) {
            $this->assertValidRelation($data['related_type'], $data['related_id'] ?? null);
        } else {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        return $data;
    }

    private function assertValidRelation(?string $type, ?int $id): void
    {
        if (!$type || !$id) {
            return;
        }

        $allowed = [Customer::class, Order::class];

        if (!in_array($type, $allowed, true)) {
            abort(422, 'نوع الربط غير مدعوم.');
        }

        $model = $type === Customer::class ? Customer::query() : Order::query();

        if (!$model->where('id', $id)->exists()) {
            abort(422, 'السجل المرتبط غير موجود.');
        }
    }
}
