@extends('admin.layout')
@section('title', 'سلة محذوفات المستخدمين')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .avatar-thumb { width:42px; height:42px; border-radius:50%; object-fit:cover; border:1px solid #eee; background:#fff; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-trash-fill me-2"></i> سلة محذوفات المستخدمين</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة حسابات العملاء التي تم حذفها مؤقتاً.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light px-4 fw-bold rounded-3"><i class="bi bi-arrow-right me-1"></i> العودة للمستخدمين</a>
    </div>

    <div class="p-4 p-lg-5">
        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="80">#</th>
                        <th class="py-3">المستخدم</th>
                        <th class="py-3">الهاتف</th>
                        <th class="py-3">تاريخ الحذف</th>
                        <th class="py-3" width="220">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="small text-muted">#{{ $user->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" class="avatar-thumb border me-3" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email ?? 'بدون بريد' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="small fw-bold">{{ $user->phone_number }}</span></td>
                            <td class="small text-muted">{{ optional($user->deleted_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @can('edit-users')
                                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> استرجاع
                                            </button>
                                        </form>
                                    @endcan
                                    @can('edit-users')
                                        <form action="{{ route('admin.users.forceDelete', $user->id) }}" method="POST" onsubmit="return confirm('حذف نهائي؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-bold">
                                                <i class="bi bi-trash-fill me-1"></i> حذف نهائي
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-center text-muted">سلة المحذوفات فارغة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
