<?php

use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RepairsPrimaryKeyAutoIncrement::ensure('personal_access_tokens');
    }

    public function down(): void
    {
        // Intentionally left blank. Reverting token table repair is unsafe.
    }
};
