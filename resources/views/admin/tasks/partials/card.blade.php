@php
    $priorityClass = match($task->priority) {
        'high' => 'danger',
        'medium' => 'warning',
        default => 'secondary',
    };
    $priorityLabel = $priorityLabels[$task->priority] ?? $task->priority;
    $isParticipant = $task->assignees->contains('id', $currentManager->id) || $task->creator_id === $currentManager->id;
    $relatedLabel = null;
    $relatedUrl = null;

    if ($task->related_model_type === \App\Models\Order::class) {
        $order = $task->relatedModel;
        $orderId = $order?->id ?? $task->related_model_id;
        if ($orderId) {
            $relatedLabel = 'طلب #' . $orderId;
            $relatedUrl = route('admin.orders.show', $orderId);
        }
    }

    if ($task->related_model_type === \App\Models\Customer::class) {
        $customer = $task->relatedModel;
        $customerName = $customer?->display_name ?? $customer?->name;
        $customerLabel = $customerName ? ($customerName . ' #' . $customer->id) : ('عميل #' . $task->related_model_id);
        $relatedLabel = $customerLabel;
        $relatedUrl = route('admin.users.show', ['user' => $task->related_model_id, 'origin' => $customer?->origin ?? \App\Models\Customer::ORIGIN_ADMIN]);
    }

    $progress = $task->progress;
@endphp

<div class="kanban-card" data-task-id="{{ $task->id }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <span class="badge bg-{{ $priorityClass }} kanban-badge">{{ $priorityLabel }}</span>
        </div>
        <span class="text-muted small">#{{ $task->id }}</span>
    </div>
    <h6 class="fw-semibold mb-2 text-truncate" title="{{ $task->title }}">{{ $task->title }}</h6>
    @if($task->description)
        <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</p>
    @endif

    <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
        <div><i class="bi bi-person-workspace me-1"></i> {{ $task->creator?->name ?? 'غير محدد' }}</div>
        @if($task->due_date)
            <div><i class="bi bi-calendar-event me-1"></i> {{ $task->due_date->translatedFormat('Y-m-d') }}</div>
        @endif
        @if($relatedLabel && $relatedUrl)
            <div>
                <i class="bi bi-link-45deg me-1"></i>
                <a href="{{ $relatedUrl }}" class="link-secondary" target="_blank" rel="noopener">{{ $relatedLabel }}</a>
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="avatar-stack">
            @foreach($task->assignees->take(4) as $assignee)
                <img src="{{ $assignee->avatar_url }}" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
            @endforeach
        </div>
        <div class="text-muted small">
            <i class="bi bi-chat-dots me-1"></i>{{ $task->comments->count() }}
        </div>
    </div>

    @if($progress)
        <div class="kanban-progress mb-2">
            <span class="bg-success" style="width: {{ $progress['percentage'] }}%"></span>
        </div>
        <div class="small text-muted mb-2">اكتمل {{ $progress['completed'] }} من {{ $progress['total'] }} مهمة فرعية</div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-2">
        <div class="d-flex gap-2">
            @if($canViewDetails)
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#taskDetailsModal-{{ $task->id }}">
                    <i class="bi bi-eye me-1"></i> تفاصيل
                </button>
            @else
                <span class="badge rounded-pill text-bg-secondary">عرض فقط</span>
            @endif

            @if($canEditTasks)
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#taskEditModal-{{ $task->id }}">
                    <i class="bi bi-pencil-square me-1"></i> تعديل
                </button>
            @endif
        </div>

        @if($canDeleteTasks)
            <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" data-confirm-message="هل ترغب بحذف هذه المهمة؟">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>
</div>

@if($canViewDetails)
<div class="modal fade task-modal" id="taskDetailsModal-{{ $task->id }}" tabindex="-1" aria-labelledby="taskDetailsModalLabel-{{ $task->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskDetailsModalLabel-{{ $task->id }}">تفاصيل المهمة #{{ $task->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h6 class="mb-3">معلومات المهمة</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4">العنوان</dt>
                            <dd class="col-sm-8">{{ $task->title }}</dd>

                            <dt class="col-sm-4">الوصف</dt>
                            <dd class="col-sm-8">{{ $task->description ?: 'لا يوجد وصف' }}</dd>

                            <dt class="col-sm-4">الأولوية</dt>
                            <dd class="col-sm-8">{{ $priorityLabels[$task->priority] ?? $task->priority }}</dd>

                            <dt class="col-sm-4">الحالة</dt>
                            <dd class="col-sm-8">{{ $statusLabels[$task->status] ?? $task->status }}</dd>

                            <dt class="col-sm-4">تاريخ الاستحقاق</dt>
                            <dd class="col-sm-8">{{ $task->due_date ? $task->due_date->translatedFormat('Y-m-d') : 'غير محدد' }}</dd>

                            <dt class="col-sm-4">المنشئ</dt>
                            <dd class="col-sm-8">{{ $task->creator?->name ?? 'غير محدد' }}</dd>

                            @if($relatedLabel && $relatedUrl)
                                <dt class="col-sm-4">ارتباط</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $relatedUrl }}" target="_blank" rel="noopener" class="link-secondary">{{ $relatedLabel }}</a>
                                </dd>
                            @endif
                        </dl>

                        <h6 class="mt-4 mb-2">المسؤولون</h6>
                        <ul class="list-unstyled small mb-0">
                            @forelse($task->assignees as $assignee)
                                <li class="d-flex align-items-center gap-2 mb-1">
                                    <img src="{{ $assignee->avatar_url }}" alt="{{ $assignee->name }}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                                    <span>{{ $assignee->name }}</span>
                                </li>
                            @empty
                                <li class="text-muted">لم يتم إسناد المهمة بعد.</li>
                            @endforelse
                        </ul>

                        <h6 class="mb-3 mt-4">المهام الفرعية</h6>
                        <div class="list-group small">
                            @forelse($task->subtasks as $subtask)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                                            @if($canEditTasks || $isParticipant)
                                                <form method="POST" action="{{ route('admin.tasks.subtasks.update', [$task, $subtask]) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="is_completed" value="{{ $subtask->is_completed ? 0 : 1 }}">
                                                    <input type="hidden" name="title" value="{{ $subtask->title }}">
                                                    <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                                        <i class="bi {{ $subtask->is_completed ? 'bi-check-square-fill text-success' : 'bi-square' }}"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <i class="bi {{ $subtask->is_completed ? 'bi-check-square-fill text-success' : 'bi-square' }}"></i>
                                            @endif

                                            @if($canEditTasks)
                                                <form method="POST" action="{{ route('admin.tasks.subtasks.update', [$task, $subtask]) }}" class="flex-grow-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="is_completed" value="{{ $subtask->is_completed ? 1 : 0 }}">
                                                    <input type="text" name="title" value="{{ $subtask->title }}" class="form-control form-control-sm" onchange="this.form.submit()">
                                                </form>
                                            @else
                                                <span class="{{ $subtask->is_completed ? 'text-decoration-line-through text-muted' : '' }}">{{ $subtask->title }}</span>
                                            @endif
                                        </div>
                                        @if($canEditTasks || $canDeleteTasks)
                                            <form method="POST" action="{{ route('admin.tasks.subtasks.destroy', [$task, $subtask]) }}" data-confirm-message="حذف المهمة الفرعية؟">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">لا توجد مهام فرعية.</div>
                            @endforelse
                        </div>
                        @if($canEditTasks || $isParticipant)
                            <form method="POST" action="{{ route('admin.tasks.subtasks.store', $task) }}" class="mt-3">
                                @csrf
                                <label class="form-label">إضافة مهمة فرعية</label>
                                <div class="input-group">
                                    <input type="text" name="title" class="form-control" placeholder="عنوان المهمة الفرعية" required>
                                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </form>
                        @endif
                    </div>
                    <div class="col-lg-6">
                        <h6 class="mb-3">التعليقات</h6>
                        <div class="list-group list-group-flush small mb-3">
                            @forelse($task->comments as $comment)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $comment->manager?->name ?? 'مستخدم' }}</strong>
                                        <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mb-1">{{ $comment->body }}</p>
                                    @if($comment->manager_id === $currentManager->id || $canDeleteTasks)
                                        <form method="POST" action="{{ route('admin.tasks.comments.destroy', [$task, $comment]) }}" data-confirm-message="حذف هذا التعليق؟">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 small">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted">لا توجد تعليقات بعد.</div>
                            @endforelse
                        </div>
                        @if(($canEditTasks || $canCommentOnTasks) && ($canEditTasks || $isParticipant))
                            <form method="POST" action="{{ route('admin.tasks.comments.store', $task) }}">
                                @csrf
                                <label class="form-label">إضافة تعليق</label>
                                <textarea name="body" class="form-control" rows="3" required placeholder="اكتب تعليقك هنا..."></textarea>
                                <div class="mt-2 text-end">
                                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-send"></i> إضافة</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($canEditTasks)
<div class="modal fade task-modal" id="taskEditModal-{{ $task->id }}" tabindex="-1" aria-labelledby="taskEditModalLabel-{{ $task->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskEditModalLabel-{{ $task->id }}">تعديل المهمة #{{ $task->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">عنوان المهمة</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">وصف المهمة</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select" required>
                                @foreach($priorityLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $task->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $task->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" class="form-control" value="{{ optional($task->due_date)->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ربط المهمة</label>
                            <div class="task-related-fields">
                                <select name="related_type" class="form-select task-related-type">
                                    @foreach($relatedTypeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $task->related_model_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="related_id" class="task-related-id" value="{{ $task->related_model_id }}">
                                <div class="task-related-select-wrapper d-none mt-2">
                                    <select class="form-select task-related-select"
                                            data-type="{{ \App\Models\Customer::class }}"
                                            data-endpoint="{{ $relatedLookupRoutes['customer'] }}"
                                            data-placeholder="اختر العميل المرتبط">
                                        @if($task->related_model_type === \App\Models\Customer::class && $relatedLabel)
                                            <option value="{{ $task->related_model_id }}" selected>{{ $relatedLabel }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="task-related-select-wrapper d-none mt-2">
                                    <select class="form-select task-related-select"
                                            data-type="{{ \App\Models\Order::class }}"
                                            data-endpoint="{{ $relatedLookupRoutes['order'] }}"
                                            data-placeholder="اختر الطلب المرتبط">
                                        @if($task->related_model_type === \App\Models\Order::class && $relatedLabel)
                                            <option value="{{ $task->related_model_id }}" selected>{{ $relatedLabel }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المسؤولون عن المهمة</label>
                            <select name="assignees[]" class="form-select" multiple {{ $canAssignTasks ? '' : 'disabled' }}>
                                @foreach($assigneeOptions as $assignee)
                                    <option value="{{ $assignee->id }}" {{ $task->assignees->contains('id', $assignee->id) ? 'selected' : '' }}>{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                            @unless($canAssignTasks)
                                <input type="hidden" name="assignees[]" value="{{ $currentManager->id }}">
                                <small class="text-muted">لا تملك صلاحية إسناد المهمة، سيتم حفظك كمكلف رئيسي.</small>
                            @endunless
                        </div>
                        @if($canEditCreator)
                            <div class="col-12">
                                <label class="form-label">منشئ المهمة</label>
                                <select name="creator_id" class="form-select">
                                    @foreach($assigneeOptions as $assignee)
                                        <option value="{{ $assignee->id }}" {{ $task->creator_id === $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif




