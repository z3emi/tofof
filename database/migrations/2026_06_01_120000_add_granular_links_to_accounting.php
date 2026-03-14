<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureMigrationsTableHasAutoIncrement();

        if (Schema::hasTable('journal_entries')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('journal_entries', 'customer_id')) {
                    $table->foreignId('customer_id')
                        ->nullable()
                        ->after('manager_id')
                        ->constrained('customers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('journal_entries', 'reference_type')) {
                    $table->string('reference_type')->nullable()->after('customer_id');
                }

                if (!Schema::hasColumn('journal_entries', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
                    $table->index(['reference_type', 'reference_id'], 'journal_entries_reference_index');
                }
            });
        }

        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table) {
                if (!Schema::hasColumn('journal_entry_lines', 'manager_id')) {
                    $table->foreignId('manager_id')
                        ->nullable()
                        ->after('account_id')
                        ->constrained('managers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('journal_entry_lines', 'customer_id')) {
                    $table->foreignId('customer_id')
                        ->nullable()
                        ->after('manager_id')
                        ->constrained('customers')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('receipt_vouchers')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('receipt_vouchers', 'approved_by')) {
                    $table->foreignId('approved_by')
                        ->nullable()
                        ->after('manager_id')
                        ->constrained('managers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('receipt_vouchers', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_vouchers', 'approved_by')) {
                    $table->foreignId('approved_by')
                        ->nullable()
                        ->after('manager_id')
                        ->constrained('managers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('payment_vouchers', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        if (Schema::hasTable('internal_transfers')) {
            Schema::table('internal_transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('internal_transfers', 'manager_id')) {
                    $table->foreignId('manager_id')
                        ->nullable()
                        ->after('notes')
                        ->constrained('managers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('internal_transfers', 'approved_by')) {
                    $table->foreignId('approved_by')
                        ->nullable()
                        ->after('manager_id')
                        ->constrained('managers')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('internal_transfers', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('internal_transfers')) {
            Schema::table('internal_transfers', function (Blueprint $table) {
                if (Schema::hasColumn('internal_transfers', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }

                if (Schema::hasColumn('internal_transfers', 'approved_by')) {
                    $table->dropConstrainedForeignId('approved_by');
                }

                if (Schema::hasColumn('internal_transfers', 'manager_id')) {
                    $table->dropConstrainedForeignId('manager_id');
                }
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('payment_vouchers', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }

                if (Schema::hasColumn('payment_vouchers', 'approved_by')) {
                    $table->dropConstrainedForeignId('approved_by');
                }
            });
        }

        if (Schema::hasTable('receipt_vouchers')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('receipt_vouchers', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }

                if (Schema::hasColumn('receipt_vouchers', 'approved_by')) {
                    $table->dropConstrainedForeignId('approved_by');
                }
            });
        }

        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table) {
                if (Schema::hasColumn('journal_entry_lines', 'customer_id')) {
                    $table->dropConstrainedForeignId('customer_id');
                }

                if (Schema::hasColumn('journal_entry_lines', 'manager_id')) {
                    $table->dropConstrainedForeignId('manager_id');
                }
            });
        }

        if (Schema::hasTable('journal_entries')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                if (Schema::hasColumn('journal_entries', 'reference_id')) {
                    $table->dropIndex('journal_entries_reference_index');
                    $table->dropColumn('reference_id');
                }

                if (Schema::hasColumn('journal_entries', 'reference_type')) {
                    $table->dropColumn('reference_type');
                }

                if (Schema::hasColumn('journal_entries', 'customer_id')) {
                    $table->dropConstrainedForeignId('customer_id');
                }
            });
        }
    }

    private function ensureMigrationsTableHasAutoIncrement(): void
    {
        if (!Schema::hasTable('migrations')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM `migrations` WHERE Field = 'id'"))->first();

        if (!$column) {
            return;
        }

        $extra = strtolower($column->Extra ?? '');

        if (str_contains($extra, 'auto_increment')) {
            return;
        }

        DB::statement('ALTER TABLE `migrations` MODIFY `id` bigint unsigned NOT NULL AUTO_INCREMENT');
    }
};
