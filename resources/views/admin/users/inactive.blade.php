@extends('admin.layout')

@section('title', 'المستخدمون غير المفعلين')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">قائمة المستخدمين في انتظار التفعيل</h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-right"></i>
            العودة لجميع المستخدمين
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted">
            هذه قائمة بالمستخدمين الذين سجلوا ولكن لم يقوموا بتفعيل حساباتهم بعد باستخدام رمز التحقق المرسل إلى واتساب.
        </p>
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>رقم الهاتف</th>
                        <th>رمز التحقق (OTP)</th>
                        <th>صلاحية الرمز تنتهي في</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->phone_number }}</td>
                            <td><strong class="fs-5 text-primary">{{ $user->whatsapp_otp }}</strong></td>
                            <td>{{ $user->whatsapp_otp_expires_at ? $user->whatsapp_otp_expires_at->diffForHumans() : 'N/A' }}</td>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">لا يوجد مستخدمون في انتظار التفعيل حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            {{ $inactiveUsers->links() }}
        </div>
    </div>
</div>
@endsection
