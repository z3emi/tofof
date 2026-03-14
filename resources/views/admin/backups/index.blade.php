@extends('admin.layout')
@section('title', 'إدارة النسخ الاحتياطي')

@push('styles')
<style>
    /* ===== START: تم إضافة هذه التنسيقات ===== */
    /* تمييز الصف المحدد بلون مختلف */
    .table-row-selected {
        background-color: #f3e5e3 !important; /* brand-secondary */
        --bs-table-accent-bg: #f3e5e3 !important; /* لضمان التوافق مع Bootstrap */
    }
    /* ===== END: تم إضافة هذه التنسيقات ===== */
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">النسخ الاحتياطية</h4>
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> إجراءات
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">
                <li><a class="dropdown-item" href="{{ route('admin.backups.create-full') }}"><i class="bi bi-file-earmark-zip-fill me-2"></i> إنشاء نسخة كاملة (.zip)</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.backups.create-db') }}"><i class="bi bi-database-down me-2"></i> إنشاء نسخة للداتا فقط (.sql)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload me-2"></i> رفع نسخة احتياطية</a></li>
                <li><button type="submit" form="delete-form" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف كل النسخ الاحتياطية المحددة؟');"><i class="bi bi-trash me-2"></i> حذف المحدد</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin.backups.settings') }}"><i class="bi bi-wrench-adjustable-circle me-2"></i> الإعدادات</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.backups.destroy') }}" method="POST" id="delete-form">
            @csrf
            @method('DELETE')
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>اسم الملف</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الحجم</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td><input type="checkbox" class="form-check-input backup-checkbox" name="backups[]" value="{{ $backup['name'] }}"></td>
                                <td class="text-start">{{ $backup['name'] }}</td>
                                <td>{{ $backup['date'] }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>
                                    <a href="{{ route('admin.backups.download', $backup['name']) }}" class="btn btn-sm btn-outline-success m-1 px-2" title="تنزيل">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if(preg_match('~\.(sql|zip)$~i', $backup['name']))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning m-1 px-2 restore-btn" 
                                            data-filename="{{ $backup['name'] }}"
                                            title="استعادة">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4">لا توجد نسخ احتياطية لعرضها.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

{{-- Upload Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form action="{{ route('admin.backups.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="uploadModalLabel">رفع ملف نسخة احتياطية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="backup_file" class="form-label fw-bold">اختر الملف (.zip أو .sql)</label>
                        <input class="form-control" type="file" id="backup_file" name="backup_file" required>
                        <div class="form-text mt-2 text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            يمكنك رفع ملفات النسخ الاحتياطية بأي حجم (يعتمد على إعدادات السيرفر).
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 10px;">إغلاق</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 10px;">رفع الملف</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Restore Premium Modal --}}
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-end" style="border-radius: 25px; overflow: hidden;">
            <div class="modal-body p-5 position-relative">
                {{-- Icon Badge --}}
                <div class="position-absolute top-0 start-0 m-4">
                    <div class="rounded-pill bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 1px solid #eee;">
                        <i class="bi bi-shield-check text-brown fs-4"></i>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-3" style="color: #2b2b2b;">استعادة نسخة احتياطية</h4>
                    <p class="text-muted mb-0 mx-auto" style="max-width: 320px; line-height: 1.6;">
                        سيتم استعادة النسخة الاحتياطية <br>
                        <span id="display-filename" class="fw-bold text-dark"></span> <br>
                        بما فيها قاعدة البيانات والملفات والصور. يمكنك اختيار استبدال البيانات الحالية لمنع التكرار. هل ترغب بالمتابعة؟
                    </p>
                </div>

                <form action="{{ route('admin.backups.restore') }}" method="POST" id="restore-form">
                    @csrf
                    <input type="hidden" name="backup_file" id="modal-backup-file">
                    
                    <div class="bg-light p-3 rounded-4 mb-3 border">
                        <div class="form-check d-flex align-items-center mb-0">
                            <input class="form-check-input ms-3" type="checkbox" name="restore_trash" id="restore_trash" value="1">
                            <label class="form-check-label flex-grow-1 text-muted" for="restore_trash">
                                استعادة السجلات المحذوفة من سلة المهملات (إن وجدت)
                            </label>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-4 mb-4 border">
                        <div class="form-check d-flex align-items-start mb-0">
                            <input class="form-check-input ms-3 mt-1" type="checkbox" name="replace_data" id="replace_data" value="1" checked>
                            <label class="form-check-label flex-grow-1 text-muted" for="replace_data">
                                استبدال البيانات الحالية قبل الاستعادة (يمنع التكرار ويعيد كل البيانات كما هي في النسخة)
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="submit" class="btn py-3 px-5 text-dark fw-bold" style="background-color: #ffc107; border-radius: 15px; flex: 1.5;">
                            متابعة الاستعادة
                        </button>
                        <button type="button" class="btn btn-outline-secondary py-3 px-5 fw-bold" data-bs-dismiss="modal" style="border-radius: 15px; border: 1px solid #dee2e6; flex: 1;">
                            إلغاء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .text-brown { color: #5a4b41; }
    .rounded-4 { border-radius: 1rem !important; }
    .form-check-input:checked { background-color: #ffc107; border-color: #ffc107; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.backup-checkbox');

    // ===== START: تم تحديث هذا السكربت بالكامل =====
    function toggleRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('table-row-selected');
        } else {
            row.classList.remove('table-row-selected');
        }
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            toggleRowHighlight(checkbox);
        });
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleRowHighlight(this);
        });
    });
    // ===== END: تم تحديث هذا السكربت بالكامل =====
    // Restore Modal Handling
    const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));
    const restoreBtns = document.querySelectorAll('.restore-btn');
    const modalBackupFile = document.getElementById('modal-backup-file');
    const displayFilename = document.getElementById('display-filename');

    restoreBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.getAttribute('data-filename');
            modalBackupFile.value = filename;
            displayFilename.textContent = filename;
            restoreModal.show();
        });
    });
});
</script>
@endpush
