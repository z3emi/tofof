@extends('admin.layout')

@section('title', 'إدارة النسخ الاحتياطي')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .selectable-row.selected { background-color: #fcecea !important; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-database-fill-check me-2"></i> إدارة النسخ الاحتياطي للأمان</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">حماية بيانات المتجر، الصور، وقواعد البيانات عبر نسخ احتياطية دورية.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-light px-4 fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-plus-circle me-1"></i> إنشاء نسخة جديدة
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius:12px">
                    <li><a class="dropdown-item py-2" href="{{ route('admin.backups.create-full') }}"><i class="bi bi-file-earmark-zip me-2"></i> نسخة كاملة (Zip)</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('admin.backups.create-db') }}"><i class="bi bi-database me-2"></i> قاعدة البيانات فقط (Sql)</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-cloud-arrow-up me-2"></i> رفع ملف خارجي</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.backups.settings') }}" class="btn btn-outline-light px-4 fw-bold"><i class="bi bi-gear-fill"></i> الإعدادات</a>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="text-muted small fw-bold">إجمالي النسخ المتوفرة: {{ count($backups) }}</div>
            <button type="submit" form="delete-form" class="btn btn-sm btn-outline-danger px-4 rounded-pill fw-bold" onclick="return confirm('حذف المحدد؟')"><i class="bi bi-trash me-1"></i> حذف النسخ المحددة</button>
        </div>

        <form action="{{ route('admin.backups.destroy') }}" method="POST" id="delete-form">
            @csrf @method('DELETE')
            <div class="table-container shadow-sm border overflow-hidden">
                <table class="table mb-0 align-middle text-center">
                    <thead class="bg-light border-bottom">
                        <tr class="text-muted small fw-bold">
                            <th class="py-3" width="50"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th class="py-3 text-start">اسم الملف</th>
                            <th class="py-3">تاريخ الإنشاء</th>
                            <th class="py-3">الحجم</th>
                            <th class="py-3" width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr class="selectable-row">
                                <td><input type="checkbox" name="backups[]" value="{{ $backup['name'] }}" class="form-check-input backup-checkbox"></td>
                                <td class="text-start fw-bold text-dark">{{ $backup['name'] }}</td>
                                <td class="small text-muted">{{ $backup['date'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $backup['size'] }}</span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.backups.download', $backup['name']) }}" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1"><i class="bi bi-download"></i></a>
                                        @if(preg_match('~\.(sql|zip)$~i', $backup['name']))
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1 restore-btn" data-filename="{{ $backup['name'] }}"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-5 text-muted">لا توجد نسخ احتياطية متاحة حالياً.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

{{-- Modals remain same logic but upgraded style --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <form action="{{ route('admin.backups.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">رفع نسخة احتياطية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">اختر الملف (.zip أو .sql)</label>
                        <input type="file" name="backup_file" class="form-control" style="border-radius:10px" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-dark w-100 py-3 fw-bold" style="border-radius:12px">بدء الرفع</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Restore Modal --}}
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-end" style="border-radius:20px; overflow:hidden">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 text-warning d-inline-flex p-3 rounded-circle mb-3"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                    <h4 class="fw-bold">تأكيد عملية الاستعادة</h4>
                    <p class="text-muted small">سيتم استبدال البيانات الحالية بالبيانات الموجودة في الملف: <br><strong id="display-filename" class="text-dark"></strong></p>
                </div>
                <form action="{{ route('admin.backups.restore') }}" method="POST">
                    @csrf <input type="hidden" name="backup_file" id="modal-backup-file">
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="form-check d-flex align-items-center m-0">
                            <input class="form-check-input ms-3" type="checkbox" name="replace_data" value="1" checked>
                            <label class="form-check-label text-muted small">استبدال البيانات الحالية (يوصى به لمنع التكرار)</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning w-100 py-3 fw-bold text-dark" style="border-radius:12px">تأكيد الاستعادة</button>
                        <button type="button" class="btn btn-light w-50 py-3 fw-bold border" data-bs-dismiss="modal" style="border-radius:12px">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sAll = document.getElementById('select-all');
    const cbs = document.querySelectorAll('.backup-checkbox');
    sAll?.addEventListener('change', function() { cbs.forEach(c => { c.checked = this.checked; c.closest('tr').classList.toggle('selected', this.checked); }); });
    cbs.forEach(c => { c.addEventListener('change', function() { this.closest('tr').classList.toggle('selected', this.checked); }); });

    const rModal = new bootstrap.Modal(document.getElementById('restoreModal'));
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const f = this.dataset.filename;
            document.getElementById('modal-backup-file').value = f;
            document.getElementById('display-filename').innerText = f;
            rModal.show();
        });
    });
});
</script>
@endsection
