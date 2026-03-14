<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\URL;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('انتهت صلاحية الجلسة، يرجى تحديث الصفحة وإعادة المحاولة.'),
                    'token' => csrf_token(),
                ], 419);
            }

            $previousUrl = URL::previous() ?: url('/');

            return redirect($previousUrl)
                ->withInput($request->except('_token'))
                ->with('error', __('انتهت صلاحية الجلسة. تم تحديث النموذج تلقائياً، يرجى المحاولة مرة أخرى.'))
                ->withErrors([
                    'session' => __('انتهت صلاحية الجلسة بسبب عدم النشاط. يرجى المحاولة مرة أخرى.'),
                ]);
        }

        return parent::render($request, $e);
    }
}
