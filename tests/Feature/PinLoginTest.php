<?php

namespace Tests\Feature;

use App\Services\Auth\PinAuthenticationService;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Tests\TestCase;

class PinLoginTest extends TestCase
{
    public function test_pin_login_returns_employee_data_and_token(): void
    {
        $employee = new class extends Model {
            protected $table = 'managers';

            protected $primaryKey = 'id';

            public $timestamps = false;

            protected $attributes = [
                'id' => 1,
                'name' => 'Test Manager',
                'department' => 'Sales',
            ];
        };

        $service = Mockery::mock(PinAuthenticationService::class);
        $service->shouldReceive('findEmployeeByPin')->once()->with('123456')->andReturn($employee);
        $service->shouldReceive('employeeHasPin')->once()->with($employee)->andReturnTrue();
        $service->shouldReceive('createApiToken')->once()->with($employee)->andReturn(['fake-token', 'Bearer']);

        $this->app->instance(PinAuthenticationService::class, $service);

        $response = $this->postJson('/api/login', [
            'pin' => '123456',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'token_type',
                'employee' => [
                    'id',
                    'name',
                    'department',
                ],
            ])
            ->assertJsonPath('token', 'fake-token')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('employee.name', 'Test Manager')
            ->assertJsonPath('employee.department', 'Sales');
    }

    public function test_pin_login_get_method_returns_method_not_allowed_json(): void
    {
        $this->getJson('/api/login')
            ->assertStatus(405)
            ->assertExactJson([
                'message' => 'يرجى استخدام طلب من نوع POST لتسجيل الدخول عبر رمز الدخول.',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
