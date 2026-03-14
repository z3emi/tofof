@extends('admin.layout')

@section('title', 'إنشاء دور جديد')

@section('content')
@can('create-roles')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">إنشاء دور جديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">اسم الدور (باللغة الإنجليزية، بدون مسافات)</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., supervisor, content-creator" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <h5 class="mt-4">الصلاحيات المتاحة</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="select_all_permissions">
                    <label class="form-check-label fw-bold" for="select_all_permissions">تحديد الكل</label>
                </div>
                <hr>
                <div class="row">
                    @forelse($permissions as $permission)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}">
                                <label class="form-check-label" for="permission_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">لا توجد صلاحيات معرفة.</p>
                    @endforelse
                </div>
            </div>

            <button type="submit" class="btn btn-primary">حفظ</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('select_all_permissions').addEventListener('click', function(event) {
        document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
</script>
@endpush
@else
<div class="alert alert-danger">ليس لديك صلاحية لإنشاء الأدوار.</div>
@endcan
@endsection
