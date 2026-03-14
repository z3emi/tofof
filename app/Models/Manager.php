<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use App\Models\Concerns\InteractsWithSanctumApiTokens;
use App\Models\AdvanceRequest;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\PayrollItem;
use App\Models\SalesCommission;
use App\Models\Task;
use App\Traits\LogsActivity;
use App\Traits\OptionalSoftDeletes;

class Manager extends Authenticatable
{
    use InteractsWithSanctumApiTokens, HasFactory, Notifiable, LogsActivity, OptionalSoftDeletes, HasPushSubscriptions;
    use HasRoles {
        hasRole as protected spatieHasRole;
    }

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'tracking_pin_hash',
        'manager_id',
        'base_salary',
        'allowances',
        'permissions',
        'commission_rate',
        'cash_on_hand',
        'salary_currency',
        'bank_account_details',
        'avatar',
        'profile_photo_path',
        'housing_card_path',
        'nationality_card_path',
        'nationality',
        'secondary_phone_number',
        'address',
        'banned_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'tracking_pin_hash',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'banned_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'cash_on_hand' => 'decimal:2',
        'permissions' => 'array',
    ];

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);

            $permissions = is_array($decoded) ? $decoded : [$permissions];
        }

        return in_array($permission, $permissions, true);
    }

    protected $guard_name = 'admin';

    public function setTrackingPin(?string $pin): void
    {
        if ($pin === null || $pin === '') {
            $this->tracking_pin_hash = null;

            return;
        }

        $this->tracking_pin_hash = \Illuminate\Support\Facades\Hash::make($pin);
    }

    public function verifyTrackingPin(?string $pin): bool
    {
        if (! $pin || ! $this->tracking_pin_hash) {
            return false;
        }

        if (\Illuminate\Support\Facades\Hash::check($pin, $this->tracking_pin_hash)) {
            return true;
        }

        if (! str_starts_with($this->tracking_pin_hash, '$2y$') && ! str_starts_with($this->tracking_pin_hash, '$argon2')) {
            return hash_equals((string) $this->tracking_pin_hash, (string) $pin);
        }

        return false;
    }

    public static function findByTrackingPin(string $pin): ?self
    {
        $plainPinMatch = static::query()
            ->where('tracking_pin_hash', $pin)
            ->first();

        if ($plainPinMatch && $plainPinMatch->verifyTrackingPin($pin)) {
            return $plainPinMatch;
        }

        foreach (static::query()->whereNotNull('tracking_pin_hash')->cursor() as $manager) {
            if ($manager->verifyTrackingPin($pin)) {
                return $manager;
            }
        }

        return null;
    }

    public function hasTrackingPin(): bool
    {
        return ! empty($this->tracking_pin_hash);
    }

    public function isSuperAdmin(): bool
    {
        if (! method_exists($this, 'roles')) {
            return false;
        }

        $roles = $this->getRelationValue('roles');

        if ($roles instanceof \Illuminate\Support\Collection) {
            return $roles->contains('name', 'Super-Admin');
        }

        return $this->roles()
            ->where('name', 'Super-Admin')
            ->exists();
    }

    public function hasRole($roles): bool
    {
        if ($roles === 'Super-Admin' || (is_array($roles) && in_array('Super-Admin', $roles, true))) {
            if ($this->isSuperAdmin()) {
                return true;
            }

            if ($roles === 'Super-Admin') {
                return false;
            }
        }

        $rolesRelation = $this->getRelationValue('roles');

        if (!$rolesRelation instanceof \Illuminate\Support\Collection) {
            $this->setRelation('roles', $this->roles()->get());
        }

        return $this->spatieHasRole($roles);
    }

    public function canAccessAdminPanel(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $storedPermissions = $this->getAttribute('permissions');
        if (is_array($storedPermissions) && in_array('view-admin-panel', $storedPermissions, true)) {
            return true;
        }

        if (method_exists($this, 'permissions')) {
            $permissionsRelation = $this->getRelationValue('permissions');

            if ($permissionsRelation instanceof Collection && $permissionsRelation->firstWhere('name', 'view-admin-panel')) {
                return true;
            }

            if ($this->permissions()->where('name', 'view-admin-panel')->exists()) {
                return true;
            }
        }

        if (method_exists($this, 'roles')) {
            return $this->roles()
                ->whereHas('permissions', function ($query) {
                    $query->where('name', 'view-admin-panel');
                })
                ->exists();
        }

        return false;
    }

    public function hasDirectPermission($permission): bool
    {
        $permissionModel = $this->resolvePermissionModel($permission);

        if (! $permissionModel) {
            return false;
        }

        $permissionsRelation = $this->getRelationValue('permissions');

        if ($permissionsRelation instanceof Collection) {
            return $permissionsRelation->contains(fn ($perm) => (int) $perm->getKey() === (int) $permissionModel->getKey());
        }

        $storedPermissions = $this->getAttribute('permissions');

        if (is_array($storedPermissions)) {
            if (in_array($permissionModel->name, $storedPermissions, true) || in_array($permissionModel->getKey(), $storedPermissions, true)) {
                return true;
            }
        }

        return $this->permissions()
            ->where($permissionModel->getKeyName(), $permissionModel->getKey())
            ->exists();
    }

    protected function resolvePermissionModel($permission): ?SpatiePermission
    {
        if ($permission instanceof SpatiePermission) {
            return $permission;
        }

        $permissionClass = app(PermissionRegistrar::class)->getPermissionClass();

        if (is_numeric($permission)) {
            return $permissionClass::find((int) $permission);
        }

        if (is_string($permission)) {
            try {
                return $permissionClass::findByName($permission, $this->getDefaultGuardName());
            } catch (PermissionDoesNotExist $e) {
                return null;
            }
        }

        return null;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id')->withTrashed();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'salesperson_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function advanceRequests(): HasMany
    {
        return $this->hasMany(AdvanceRequest::class, 'employee_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_id');
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'employee_id');
    }

    public function salesCommissions(): HasMany
    {
        return $this->hasMany(SalesCommission::class, 'employee_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'manager_task')->withTimestamps();
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(Customer::class, 'manager_id')->withTrashed();
    }

    public function governorateAssignments(): HasMany
    {
        return $this->hasMany(ManagerGovernorate::class, 'user_id');
    }

    public function assignedGovernorates(): array
    {
        return $this->governorateAssignments
            ->pluck('governorate')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function syncGovernorates(array $governorates): void
    {
        $normalized = collect($governorates)
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $current = $this->governorateAssignments()->pluck('governorate');

        $toDelete = $current->diff($normalized);
        if ($toDelete->isNotEmpty()) {
            $this->governorateAssignments()->whereIn('governorate', $toDelete)->delete();
        }

        $toInsert = $normalized->diff($current);
        foreach ($toInsert as $governorate) {
            $this->governorateAssignments()->create(['governorate' => $governorate]);
        }
    }

    public function isProtectedSuperAdmin(): bool
    {
        $phone = (string) config('admin.super_admin_phone', 'admin');

        return $this->phone_number === $phone && $this->isSuperAdmin();
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('manager_id');
    }

    public function teamMemberIds(bool $includeSelf = true): array
    {
        $allManagers = self::query()->get(['id', 'manager_id']);
        $grouped = $allManagers->groupBy('manager_id');

        $ids = $includeSelf ? [$this->id] : [];
        $stack = $grouped->get($this->id, collect())->pluck('id')->all();

        while (!empty($stack)) {
            $childId = array_pop($stack);
            if (!in_array($childId, $ids, true)) {
                $ids[] = $childId;
                $stack = array_merge($stack, $grouped->get($childId, collect())->pluck('id')->all());
            }
        }

        return $ids;
    }

    public function accessibleOrderUserIds(): array
    {
        if ($this->can('view-all-orders')) {
            return [];
        }

        if ($this->can('view-team-orders')) {
            return $this->teamMemberIds();
        }

        return [$this->id];
    }

    public function accessibleCustomerGovernorates(): array
    {
        if ($this->can('view-all-customer-zones')) {
            return [];
        }

        return $this->assignedGovernorates();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo_url) {
            return $this->profile_photo_url;
        }

        $val = $this->avatar;

        if (!empty($val) && str_starts_with($val, 'http')) {
            return $val;
        }

        if (!empty($val) && Storage::disk('public')->exists($val)) {
            return asset('storage/' . $val);
        }

        return asset('storage/avatars/default.jpg');
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (empty($this->profile_photo_path)) {
            return null;
        }

        if (str_starts_with($this->profile_photo_path, 'http')) {
            return $this->profile_photo_path;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function getHousingCardUrlAttribute(): ?string
    {
        if (empty($this->housing_card_path)) {
            return null;
        }

        if (str_starts_with($this->housing_card_path, 'http')) {
            return $this->housing_card_path;
        }

        return Storage::disk('public')->url($this->housing_card_path);
    }

    public function getNationalityCardUrlAttribute(): ?string
    {
        if (empty($this->nationality_card_path)) {
            return null;
        }

        if (str_starts_with($this->nationality_card_path, 'http')) {
            return $this->nationality_card_path;
        }

        return Storage::disk('public')->url($this->nationality_card_path);
    }

    public function hasIncompleteProfile(): bool
    {
        return count($this->missingProfileFields()) > 0;
    }

    public function missingProfileFields(): array
    {
        $fields = [];

        if (!$this->profile_photo_path) {
            $fields[] = 'الصورة الشخصية';
        }

        if (!$this->nationality) {
            $fields[] = 'الجنسية';
        }

        if (!$this->phone_number) {
            $fields[] = 'رقم الهاتف الأساسي';
        }

        if (!$this->address) {
            $fields[] = 'عنوان السكن';
        }

        if (!$this->housing_card_path) {
            $fields[] = 'صورة بطاقة السكن';
        }

        if (!$this->nationality_card_path) {
            $fields[] = 'صورة الجنسية';
        }

        return $fields;
    }
}
