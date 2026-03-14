<?php

namespace Tests\Feature;

use App\Models\Manager;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    public function test_manager_with_permissions_can_access_orders_endpoints(): void
    {
        $manager = $this->makeManager([
            'id' => 1,
            'name' => 'مدير النظام',
            'email' => 'manager@example.com',
            'permissions' => ['view_orders', 'view_products'],
        ]);

        Sanctum::actingAs($manager, ['*'], 'sanctum');

        $indexResponse = $this->getJson('/api/orders');
        $indexResponse->assertOk();
        $indexResponse->assertJson([
            'success' => true,
        ]);

        $showResponse = $this->getJson('/api/orders/101');
        $showResponse->assertOk();
        $showResponse->assertJson([
            'success' => true,
            'data' => [
                'id' => 101,
            ],
        ]);
    }

    public function test_manager_without_required_permission_gets_forbidden_response(): void
    {
        $manager = $this->makeManager([
            'id' => 2,
            'name' => 'مدير بدون صلاحيات',
            'email' => 'limited@example.com',
            'permissions' => ['view_products'],
        ]);

        Sanctum::actingAs($manager, ['*'], 'sanctum');

        $response = $this->getJson('/api/orders');

        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'ليس لديك صلاحية للوصول إلى هذا القسم',
        ]);
    }

    public function test_manager_can_access_allowed_resources_only(): void
    {
        $manager = $this->makeManager([
            'id' => 3,
            'name' => 'مدير المنتجات',
            'email' => 'products@example.com',
            'permissions' => ['view_products'],
        ]);

        Sanctum::actingAs($manager, ['*'], 'sanctum');

        $allowedResponse = $this->getJson('/api/products');
        $allowedResponse->assertOk();
        $allowedResponse->assertJson([
            'success' => true,
        ]);

        $forbiddenResponse = $this->getJson('/api/reports');
        $forbiddenResponse->assertForbidden();
        $forbiddenResponse->assertJson([
            'success' => false,
        ]);
    }

    private function makeManager(array $attributes): Manager
    {
        $manager = new Manager();
        $manager->forceFill(array_merge([
            'id' => 999,
            'name' => 'مدير تجريبي',
            'email' => 'placeholder@example.com',
            'permissions' => [],
        ], $attributes));

        $manager->exists = true;

        return $manager;
    }
}
