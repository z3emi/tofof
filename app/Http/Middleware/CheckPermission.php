<?php

namespace App\Http\Middleware;

use App\Models\Manager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * @var array<string, string>
     */
    private array $permissionsMap = [
        'orders.index' => 'view_orders',
        'orders.show' => 'view_orders',
        'products.index' => 'view_products',
        'customers.index' => 'view_customers',
        'reports.index' => 'view_reports',
        'notifications.index' => 'view_notifications',
        'settings.index' => 'view_settings',
    ];

    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $user = $request->user();

        if (! $user instanceof Manager) {
            return $this->forbiddenResponse();
        }

        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return $next($request);
        }

        $requiredPermission = $this->permissionsMap[$routeName] ?? null;

        if (! $requiredPermission) {
            return $next($request);
        }

        if (! $user->hasPermission($requiredPermission)) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('ليس لديك صلاحية للوصول إلى هذا القسم'),
        ], Response::HTTP_FORBIDDEN);
    }
}
