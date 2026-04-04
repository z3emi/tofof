<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    /**
     * Log any authenticated admin request to the activity log.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $intendedMethod = strtoupper((string) ($request->input('_method') ?: $request->method()));

        if ($request->isMethod('OPTIONS')) {
            return $response;
        }

        // لا نسجل زيارات الصفحات (GET/HEAD) لتجنب امتلاء السجل بإدخالات admin_view.
        if (! in_array($intendedMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($this->shouldIgnoreRoute($routeName)) {
            return $response;
        }

        $managerId = auth('admin')->id();
        if (! $managerId) {
            return $response;
        }

        $action = $this->resolveAction($intendedMethod, $routeName, (string) $request->path());

        ActivityLog::record([
            'user_id'       => $managerId,
            // أحداث التتبع الخلفية لا تُربط بموديل محدد حتى لا تظهر كسجل كيان فعلي.
            'loggable_id'   => null,
            'loggable_type' => null,
            'action'        => $action,
            'before'        => null,
            'after'         => [
                'method' => $intendedMethod,
                'route_name' => $routeName,
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'payload' => $this->sanitizePayload($request, $intendedMethod),
            ],
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);

        return $response;
    }

    private function resolveAction(string $method, string $routeName = '', string $path = ''): string
    {
        $method = strtoupper($method);

        if (in_array($method, ['PUT', 'PATCH'], true)) {
            return 'admin_update';
        }

        if ($method === 'DELETE') {
            return 'admin_delete';
        }

        if ($method !== 'POST') {
            return 'admin_action';
        }

        $route = strtolower($routeName);
        $path = strtolower($path);

        if ($this->containsAny($route, ['destroy', 'delete']) || $this->containsAny($path, ['destroy', 'delete', 'force-delete'])) {
            return 'admin_delete';
        }

        if ($this->containsAny($route, ['update', 'toggle', 'restore', 'mark', 'clear', 'ban', 'unban', 'regenerate', 'move', 'apply', 'assign', 'sync'])
            || $this->containsAny($path, ['update', 'toggle', 'restore', 'mark', 'clear', 'ban', 'unban', 'regenerate', 'move', 'apply', 'assign', 'sync'])) {
            return 'admin_update';
        }

        return 'admin_create';
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function shouldIgnoreRoute(string $routeName): bool
    {
        $ignored = [
            'admin.push_subscriptions.update',
            'admin.push_subscriptions.destroy',
            'admin.notifications.markAsRead',
            'admin.notifications.markAllRead',
            'admin.notifications.clearAll',
        ];

        return in_array($routeName, $ignored, true);
    }

    private function sanitizePayload(Request $request, string $method): array
    {
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return [];
        }

        return $request->except([
            'password',
            'password_confirmation',
            'current_password',
            '_token',
            '_method',
        ]);
    }
}
