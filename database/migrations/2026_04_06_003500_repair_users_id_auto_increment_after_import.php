<?php

use Illuminate\Database\Migrations\Migration;
use App\Support\RepairsPrimaryKeyAutoIncrement;

return new class extends Migration {
    public function up(): void
    {
        RepairsPrimaryKeyAutoIncrement::ensure('users');
    }

    public function down(): void
    {
        // Intentionally left blank. Reverting AUTO_INCREMENT on users.id is unsafe.
    }
};
