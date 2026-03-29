<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix auto_increment if missing (Common in some MySQL setups/transfers)
        try {
            DB::statement('ALTER TABLE permissions MODIFY id BIGINT(20) UNSIGNED AUTO_INCREMENT');
        } catch (\Exception $e) {
            // Might already be auto_increment, just log or ignore
        }

        // 2. Add Missing permissions
        $perms = [
            'view-primary-categories', 'create-primary-categories', 'edit-primary-categories', 'delete-primary-categories',
            'view-gifts', 'create-gifts', 'edit-gifts', 'delete-gifts',
            'view-blog-categories', 'create-blog-categories', 'edit-blog-categories', 'delete-blog-categories',
            'manage-whatsapp', 'manage-slides', 'edit-settings-frontend', 'edit-settings-seo'
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'admin');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Remove permissions in reverse
    }
};
