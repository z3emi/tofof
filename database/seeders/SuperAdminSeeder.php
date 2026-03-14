<?php

namespace Database\Seeders;

use App\Models\Manager;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $phone = (string) config('admin.super_admin_phone', 'admin');
        $email = (string) config('admin.super_admin_email', 'admin@tofof.test');
        $password = (string) config('admin.super_admin_password', 'admin');

        $manager = Manager::updateOrCreate(
            ['phone_number' => $phone],
            [
                'name' => 'Super Admin',
                'email' => $email,
                'password' => $password,
                'phone_verified_at' => now(),
            ]
        );

        if (!$manager->hasRole('Super-Admin')) {
            $manager->assignRole('Super-Admin');
        }
    }
}
