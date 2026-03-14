@extends('admin.layout')

@section('title', 'إدارة الأدوار والصلاحيات')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">جميع الأدوار</h4>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>
            إنشاء دور جديد
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>اسم الدور</th>
                        <th>عدد الصلاحيات</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td><span class="badge bg-info">{{ $role->permissions->count() }}</span></td>
                            <td>
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary">تعديل الصلاحيات</a>
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد؟ سيتم حذف هذا الدور من جميع المستخدمين.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">لا توجد أدوار لعرضها.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection