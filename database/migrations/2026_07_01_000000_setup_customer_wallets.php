<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('journal_entry_lines')) {
            Schema::drop('journal_entry_lines');
        }

        if (Schema::hasTable('journal_entries')) {
            Schema::drop('journal_entries');
        }

        if (Schema::hasTable('accounts')) {
            Schema::drop('accounts');
        }

        if (Schema::hasTable('system_accounts')) {
            Schema::drop('system_accounts');
        }

        if (Schema::hasTable('cash_accounts')) {
            Schema::drop('cash_accounts');
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::drop('payment_vouchers');
        }

        if (Schema::hasTable('internal_transfers')) {
            Schema::drop('internal_transfers');
        }

        if (!Schema::hasColumn('customers', 'balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('balance', 18, 2)->default(0)->after('notes');
            });
        }

        if (!Schema::hasColumn('managers', 'cash_on_hand')) {
            Schema::table('managers', function (Blueprint $table) {
                $table->decimal('cash_on_hand', 18, 2)->default(0)->after('commission_rate');
            });
        }

        if (!Schema::hasTable('cash_boxes')) {
            Schema::create('cash_boxes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('balance', 18, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cash_box_transactions')) {
            Schema::create('cash_box_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_box_id')->constrained()->cascadeOnDelete();
                $table->nullableMorphs('related_model');
                $table->string('type');
                $table->decimal('amount', 18, 2);
                $table->decimal('balance_after', 18, 2);
                $table->text('description')->nullable();
                $table->timestamp('transaction_date')->useCurrent();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_transactions')) {
            Schema::create('customer_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->nullableMorphs('related_model');
                $table->string('type');
                $table->decimal('amount', 18, 2);
                $table->decimal('balance_after', 18, 2);
                $table->text('description')->nullable();
                $table->timestamp('transaction_date')->useCurrent();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('receipt_vouchers')) {
            Schema::create('receipt_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->date('voucher_date');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('cash_box_id')->nullable()->constrained('cash_boxes')->nullOnDelete();
                $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->decimal('amount', 18, 2);
                $table->text('description')->nullable();
                $table->string('transaction_channel')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('receipt_vouchers')) {
            if (Schema::hasColumn('receipt_vouchers', 'cash_account_id')) {
                $this->dropForeignIfExists('receipt_vouchers', 'receipt_vouchers_cash_account_id_foreign');
            }

            if (Schema::hasColumn('receipt_vouchers', 'account_id')) {
                $this->dropForeignIfExists('receipt_vouchers', 'receipt_vouchers_account_id_foreign');
            }

            if (Schema::hasColumn('receipt_vouchers', 'journal_entry_id')) {
                $this->dropForeignIfExists('receipt_vouchers', 'receipt_vouchers_journal_entry_id_foreign');
            }

            Schema::table('receipt_vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('receipt_vouchers', 'cash_account_id')) {
                    $table->dropColumn('cash_account_id');
                }

                if (Schema::hasColumn('receipt_vouchers', 'account_id')) {
                    $table->dropColumn('account_id');
                }

                if (Schema::hasColumn('receipt_vouchers', 'journal_entry_id')) {
                    $table->dropColumn('journal_entry_id');
                }

                if (Schema::hasColumn('receipt_vouchers', 'currency_code')) {
                    $table->dropColumn('currency_code');
                }

                if (Schema::hasColumn('receipt_vouchers', 'currency_amount')) {
                    $table->dropColumn('currency_amount');
                }

                if (Schema::hasColumn('receipt_vouchers', 'exchange_rate')) {
                    $table->dropColumn('exchange_rate');
                }

                if (!Schema::hasColumn('receipt_vouchers', 'cash_box_id')) {
                    $table->foreignId('cash_box_id')->nullable()->after('voucher_date')->constrained('cash_boxes')->nullOnDelete();
                }

                if (!Schema::hasColumn('receipt_vouchers', 'manager_id')) {
                    $table->foreignId('manager_id')->nullable()->after('cash_box_id')->constrained('managers')->nullOnDelete();
                }

                if (!Schema::hasColumn('receipt_vouchers', 'collector_id')) {
                    $table->unsignedBigInteger('collector_id')->nullable()->after('manager_id');
                }

                if (!Schema::hasColumn('receipt_vouchers', 'transaction_channel')) {
                    $table->string('transaction_channel')->nullable()->after('description');
                }
            });
        }

        if (!Schema::hasTable('deposit_vouchers')) {
            Schema::create('deposit_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->date('voucher_date');
                $table->foreignId('cash_box_id')->constrained('cash_boxes');
                $table->foreignId('manager_id')->constrained('managers');
                $table->decimal('amount', 18, 2);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('invoices')) {
            if (Schema::hasColumn('invoices', 'cash_account_id')) {
                $this->dropForeignIfExists('invoices', 'invoices_cash_account_id_foreign');
            }

            if (Schema::hasColumn('invoices', 'journal_entry_id')) {
                $this->dropForeignIfExists('invoices', 'invoices_journal_entry_id_foreign');
            }

            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'cash_account_id')) {
                    $table->dropColumn('cash_account_id');
                }

                if (Schema::hasColumn('invoices', 'journal_entry_id')) {
                    $table->dropColumn('journal_entry_id');
                }
            });
        }

        if (Schema::hasTable('invoice_payments')) {
            if (Schema::hasColumn('invoice_payments', 'cash_account_id')) {
                $this->dropForeignIfExists('invoice_payments', 'invoice_payments_cash_account_id_foreign');
            }

            if (Schema::hasColumn('invoice_payments', 'journal_entry_id')) {
                $this->dropForeignIfExists('invoice_payments', 'invoice_payments_journal_entry_id_foreign');
            }

            Schema::table('invoice_payments', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_payments', 'cash_account_id')) {
                    $table->dropColumn('cash_account_id');
                }

                if (Schema::hasColumn('invoice_payments', 'journal_entry_id')) {
                    $table->dropColumn('journal_entry_id');
                }
            });
        }

        if (Schema::hasTable('hr_payrolls')) {
            if (Schema::hasColumn('hr_payrolls', 'journal_entry_id')) {
                $this->dropForeignIfExists('hr_payrolls', 'hr_payrolls_journal_entry_id_foreign');
            }

            Schema::table('hr_payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('hr_payrolls', 'journal_entry_id')) {
                    $table->dropColumn('journal_entry_id');
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    private function dropForeignIfExists(string $table, string $foreignKey): void
    {
        if ($this->foreignKeyExists($table, $foreignKey)) {
            Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$database, $table, $foreignKey]
        );

        return !empty($result);
    }

    public function down(): void
    {
        if (Schema::hasTable('deposit_vouchers')) {
            Schema::drop('deposit_vouchers');
        }

        if (Schema::hasTable('cash_box_transactions')) {
            Schema::drop('cash_box_transactions');
        }

        if (Schema::hasTable('cash_boxes')) {
            Schema::drop('cash_boxes');
        }

        if (Schema::hasTable('customer_transactions')) {
            Schema::drop('customer_transactions');
        }

        if (Schema::hasColumn('customers', 'balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('balance');
            });
        }

        if (Schema::hasColumn('managers', 'cash_on_hand')) {
            Schema::table('managers', function (Blueprint $table) {
                $table->dropColumn('cash_on_hand');
            });
        }
    }
};
