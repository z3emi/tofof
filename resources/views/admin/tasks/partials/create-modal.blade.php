<div class="modal fade task-modal" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaskModalLabel">إنشاء مهمة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('admin.tasks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">عنوان المهمة</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">وصف المهمة</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="تفاصيل مختصرة"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select" required>
                                @foreach($priorityLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $value === 'medium' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $value === \App\Models\Task::STATUS_TODO ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ربط المهمة</label>
                            <div class="task-related-fields">
                                <select name="related_type" class="form-select task-related-type">
                                    @foreach($relatedTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="related_id" class="task-related-id">
                                <div class="task-related-select-wrapper d-none mt-2">
                                    <select class="form-select task-related-select"
                                            data-type="{{ \App\Models\Customer::class }}"
                                            data-endpoint="{{ $relatedLookupRoutes['customer'] }}"
                                            data-placeholder="اختر العميل المرتبط"></select>
                                </div>
                                <div class="task-related-select-wrapper d-none mt-2">
                                    <select class="form-select task-related-select"
                                            data-type="{{ \App\Models\Order::class }}"
                                            data-endpoint="{{ $relatedLookupRoutes['order'] }}"
                                            data-placeholder="اختر الطلب المرتبط"></select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المسؤولون عن المهمة</label>
                            <select name="assignees[]" class="form-select" multiple {{ $canAssignTasks ? '' : 'disabled' }}>
                                @foreach($assigneeOptions as $assignee)
                                    <option value="{{ $assignee->id }}" {{ $assignee->id === $currentManager->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                            @unless($canAssignTasks)
                                <input type="hidden" name="assignees[]" value="{{ $currentManager->id }}">
                                <small class="text-muted">سيتم تعيينك تلقائياً كمسؤول عن المهمة.</small>
                            @endunless
                        </div>
                        @if($canEditCreator)
                            <div class="col-12">
                                <label class="form-label">منشئ المهمة</label>
                                <select name="creator_id" class="form-select">
                                    @foreach($assigneeOptions as $assignee)
                                        <option value="{{ $assignee->id }}" {{ $assignee->id === $currentManager->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> حفظ المهمة</button>
                </div>
            </form>
        </div>
    </div>
</div>
