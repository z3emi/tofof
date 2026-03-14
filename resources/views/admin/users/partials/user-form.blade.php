@php($isEdit = isset($user))

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">الاسم الكامل</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', optional($user)->name) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone_number" class="form-label">رقم الهاتف</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', optional($user)->phone_number) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', optional($user)->email) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">كلمة المرور {{ $isEdit ? '(اتركها فارغة للإبقاء على الحالية)' : '' }}</label>
        <input type="password" class="form-control" id="password" name="password" {{ $isEdit ? '' : 'required' }}>
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ $isEdit ? '' : 'required' }}>
    </div>
    <div class="col-md-6 mb-3">
        <label for="governorate" class="form-label">المحافظة</label>
        <select id="governorate" name="governorate" class="form-select">
            <option value="">بدون تحديد</option>
            @foreach ($governorates as $governorate)
                <option value="{{ $governorate }}" @selected(old('governorate', optional($user)->governorate) === $governorate)>{{ $governorate }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="city" class="form-label">المدينة / القضاء</label>
        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', optional($user)->city) }}">
    </div>
    <div class="col-12 mb-3">
        <label for="address" class="form-label">العنوان التفصيلي</label>
        <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', optional($user)->address) }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="phone_verified" name="phone_verified" value="1" @checked(old('phone_verified', optional($user)->phone_verified_at ? 1 : 0))>
            <label class="form-check-label" for="phone_verified">تفعيل رقم الهاتف فوراً</label>
        </div>
        <small class="text-muted">بتفعيل هذا الخيار سيتمكن المستخدم من تسجيل الدخول دون التحقق عبر واتساب.</small>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_banned" name="is_banned" value="1" @checked(old('is_banned', optional($user)->banned_at ? 1 : 0))>
            <label class="form-check-label" for="is_banned">وضع المستخدم في قائمة الحظر</label>
        </div>
        <small class="text-muted">يمكنك تغيير حالة الحظر لاحقاً من جدول المستخدمين أيضاً.</small>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i>
        {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء المستخدم' }}
    </button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إلغاء</a>
</div>
