<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RunAutomaticBackupWithoutCron;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: base_path('routes/web.php'),
        commands: base_path('routes/console.php'),
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
            RunAutomaticBackupWithoutCron::class,
        ]);
        
        // Add exceptions for your webhook routes here
        $middleware->validateCsrfTokens(except: [
            'whatsapp-webhook', // For Facebook's initial verification
            'receive-whatsapp'  // For receiving messages from Pipedream
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration
    })->create()->useLangPath(dirname(__DIR__) . '/lang');