<?php

return [
    App\Providers\AppServiceProvider::class,

    // --- ADD THIS LINE ---
    Spatie\Permission\PermissionServiceProvider::class,
    App\Providers\RouteServiceProvider::class,

    // إذا عندك AuthServiceProvider خليه، إذا ما موجود احذفه
    App\Providers\AuthServiceProvider::class,

    // أضف EventServiceProvider اللي سوّيناه للتسجيلات (Login/Logout/Failed)
    App\Providers\EventServiceProvider::class,
];