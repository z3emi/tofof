<?php
// ======================================================================
// الملف: database/seeders/DatabaseSeeder.php
// هذا الملف مسؤول عن تشغيل كل الـ Seeders
// ======================================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // استدعاء الـ Seeder المحدث للأدوار والصلاحيات
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}