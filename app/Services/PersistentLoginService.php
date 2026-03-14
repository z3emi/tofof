<?php

namespace App\Services;

use App\Models\PersistentLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class PersistentLoginService
{
    // اسم الكوكي المخصص
    const COOKIE_NAME = 'tofof_remember';

    // مدّة التذكّر (أشهر)
    protected int $months = 12;

    public function issue(int $userId, string $ip = null, string $userAgent = null): void
    {
        // نصين: selector (public) + validator (secret, نرسل plain بالكوكي، نخزن hash)
        $selector  = Str::random(24);
        $validator = Str::random(64);
        $hash      = password_hash($validator, PASSWORD_BCRYPT);

        // خزّن بالسجل
        PersistentLogin::create([
            'user_id'        => $userId,
            'selector'       => $selector,
            'validator_hash' => $hash,
            'ip'             => $ip,
            'user_agent'     => $userAgent,
            'last_used_at'   => now(),
        ]);

        // خزّن بالكوكي: selector:validator (نوقّته ونخليه HttpOnly + Secure)
        $value = $selector . ':' . $validator;

        $minutes = Carbon::now()->addMonths($this->months)->diffInMinutes();
        Cookie::queue(
            cookie(
                self::COOKIE_NAME,
                $value,
                $minutes,
                '/',
                config('session.domain'),   // يشتغل حتى لو null
                config('session.secure', true),
                true,                       // httpOnly
                false,                      // raw
                config('session.same_site', 'lax')
            )
        );
    }

    public function attemptAutoLogin(): bool
    {
        if (Auth::check()) {
            return true;
        }

        $cookie = request()->cookie(self::COOKIE_NAME);
        if (!$cookie || !str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        $record = PersistentLogin::where('selector', $selector)->first();

        if (!$record) {
            return false;
        }

        // تحقّق من ال validator
        if (!password_verify($validator, $record->validator_hash)) {
            // محتمل سرقة — احذف السجل والكوكي
            $this->invalidateRecord($record);
            return false;
        }

        // نجاح → سجل دخول + دوّر (rotate) الvalidator (حماية)
        Auth::loginUsingId($record->user_id, true);

        $this->rotateValidator($record);

        return true;
    }

    public function invalidateForUser(int $userId): void
    {
        PersistentLogin::where('user_id', $userId)->delete();
        Cookie::queue(Cookie::forget(self::COOKIE_NAME, '/', config('session.domain'), config('session.secure', true), config('session.same_site', 'lax')));
    }

    public function invalidateCurrentCookie(): void
    {
        $cookie = request()->cookie(self::COOKIE_NAME);
        if ($cookie && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            PersistentLogin::where('selector', $selector)->delete();
        }
        Cookie::queue(Cookie::forget(self::COOKIE_NAME, '/', config('session.domain'), config('session.secure', true), config('session.same_site', 'lax')));
    }

    protected function rotateValidator(PersistentLogin $record): void
    {
        $newValidator = Str::random(64);
        $record->validator_hash = password_hash($newValidator, PASSWORD_BCRYPT);
        $record->last_used_at   = now();
        $record->save();

        $value   = $record->selector . ':' . $newValidator;
        $minutes = Carbon::now()->addMonths($this->months)->diffInMinutes();

        Cookie::queue(
            cookie(
                self::COOKIE_NAME,
                $value,
                $minutes,
                '/',
                config('session.domain'),
                config('session.secure', true),
                true,
                false,
                config('session.same_site', 'lax')
            )
        );
    }

    protected function invalidateRecord(PersistentLogin $record): void
    {
        $record->delete();
        Cookie::queue(Cookie::forget(self::COOKIE_NAME, '/', config('session.domain'), config('session.secure', true), config('session.same_site', 'lax')));
    }
}
