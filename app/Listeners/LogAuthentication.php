<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\Manager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

class LogAuthentication
{
    public function handle($event): void
    {
        if ($event instanceof Login) {
            $user = $event->user;
            $managerId = $user instanceof Manager ? $user->id : null;

            ActivityLog::create([
                'user_id'       => $managerId,
                'loggable_id'   => $user?->id,
                'loggable_type' => $user ? get_class($user) : null,
                'action'        => 'login',
                'after'         => ['email' => $user?->email],
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent(),
            ]);
        } elseif ($event instanceof Logout) {
            $user = $event->user;
            $managerId = $user instanceof Manager ? $user->id : null;

            ActivityLog::create([
                'user_id'       => $managerId,
                'loggable_id'   => $user?->id,
                'loggable_type' => $user ? get_class($user) : null,
                'action'        => 'logout',
                'after'         => ['email' => $user?->email],
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent(),
            ]);
        } elseif ($event instanceof Failed) {
            $user = $event->user; // قد يكون null
            $managerId = $user instanceof Manager ? $user->id : null;

            ActivityLog::create([
                'user_id'       => $managerId,
                'loggable_id'   => $user?->id,
                'loggable_type' => $user ? get_class($user) : null,
                'action'        => 'failed_login',
                'after'         => ['email' => $event->credentials['email'] ?? null],
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent(),
            ]);
        }
    }
}
