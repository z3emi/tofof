@extends('admin.layout')
@section('title', 'سلة محذوفات المستخدمين')

@push('styles')
<style>
  :root{ --brand:#cd8985; --line:#eadbcd; }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }
  .avatar-thumb{ width:48px; height:48px; border-radius:50%; object-fit:cover; border:1px solid #eee; background:#fff; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات المستخدمين</h4>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-right me-1"></i> العودة للمستخدمين
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>الصورة</th>
            <th>الاسم</th>
            <th>الهاتف</th>
            <th>تاريخ الحذف</th>
            <th>العمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>{{ $user->id }}</td>
              <td>
                @if($user->avatar)
                  <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="avatar-thumb">
                @else
                  <div class="avatar-thumb d-flex align-items-center justify-content-center text-muted m-auto"><i class="bi bi-person"></i></div>
                @endif
              </td>
              <td class="text-start fw-bold">{{ $user->name }}</td>
              <td>{{ $user->phone_number }}</td>
              <td>{{ optional($user->deleted_at)->format('Y-m-d H:i') }}</td>
              <td>
                @can('edit-users')
                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                  </button>
                </form>
                @endcan

                @can('edit-users')
                <form action="{{ route('admin.users.forceDelete', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح حساب المستخدم نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                    <i class="bi bi-trash-fill"></i> حذف نهائي
                  </button>
                </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-4 text-muted">سلة المحذوفات فارغة.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection
