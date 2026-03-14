<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// أحداث التوثيق
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

// المستمع اللي أنشأناه سابقًا
use App\Listeners\LogAuthentication;

class EventServiceProvider extends ServiceProvider
{
    /**
     * ربط الأحداث بالمستمعين
     */
    protected $listen = [
        Login::class  => [ LogAuthentication::class ],
        Logout::class => [ LogAuthentication::class ],
        Failed::class => [ LogAuthentication::class ],
    ];

    public function boot(): void
    {
        //
    }

    /**
     * عادةً نخليها false حتى ما يكتشف تلقائيًا
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
