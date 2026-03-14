<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('managers')) {
            Schema::table('managers', function (Blueprint $table) {
                if (!Schema::hasColumn('managers', 'base_salary')) {
                    $table->decimal('base_salary', 12, 2)->default(0)->after('manager_id');
                }

                if (!Schema::hasColumn('managers', 'allowances')) {
                    $table->decimal('allowances', 12, 2)->default(0)->after('base_salary');
                }

                if (!Schema::hasColumn('managers', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 4)->default(0)->after('allowances');
                }

                if (!Schema::hasColumn('managers', 'bank_account_details')) {
                    $table->text('bank_account_details')->nullable()->after('commission_rate');
                }
            });
        }

        if (Schema::hasColumn('hr_payroll_items', 'user_id')) {
            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->renameColumn('user_id', 'employee_id');
            });

            DB::table('hr_payroll_items')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($items) {
                    foreach ($items as $item) {
                        $managerId = null;

                        $user = DB::table('users')
                            ->select(['manager_id', 'phone_number'])
                            ->where('id', $item->employee_id)
                            ->first();

                        if ($user) {
                            $managerId = $user->manager_id;

                            if (!$managerId && $user->phone_number) {
                                $managerId = DB::table('managers')
                                    ->where('phone_number', $user->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_payroll_items')
                            ->where('id', $item->id)
                            ->update(['employee_id' => $managerId]);
                    }
                });

            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('hr_leave_requests', 'user_id')) {
            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->renameColumn('user_id', 'employee_id');
            });

            DB::table('hr_leave_requests')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($requests) {
                    foreach ($requests as $request) {
                        $managerId = null;

                        $user = DB::table('users')
                            ->select(['manager_id', 'phone_number'])
                            ->where('id', $request->employee_id)
                            ->first();

                        if ($user) {
                            $managerId = $user->manager_id;

                            if (!$managerId && $user->phone_number) {
                                $managerId = DB::table('managers')
                                    ->where('phone_number', $user->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_leave_requests')
                            ->where('id', $request->id)
                            ->update(['employee_id' => $managerId]);
                    }
                });

            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('hr_advance_requests', 'user_id')) {
            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->renameColumn('user_id', 'employee_id');
            });

            DB::table('hr_advance_requests')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($requests) {
                    foreach ($requests as $request) {
                        $managerId = null;

                        $user = DB::table('users')
                            ->select(['manager_id', 'phone_number'])
                            ->where('id', $request->employee_id)
                            ->first();

                        if ($user) {
                            $managerId = $user->manager_id;

                            if (!$managerId && $user->phone_number) {
                                $managerId = DB::table('managers')
                                    ->where('phone_number', $user->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_advance_requests')
                            ->where('id', $request->id)
                            ->update(['employee_id' => $managerId]);
                    }
                });

            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'base_salary')) {
            DB::table('users')
                ->whereNotNull('base_salary')
                ->where('base_salary', '>', 0)
                ->orderBy('id')
                ->chunkById(500, function ($users) {
                    foreach ($users as $user) {
                        $managerId = DB::table('managers')
                            ->where('phone_number', $user->phone_number)
                            ->value('id');

                        if ($managerId) {
                            DB::table('managers')
                                ->where('id', $managerId)
                                ->update([
                                    'base_salary' => $user->base_salary,
                                    'allowances' => $user->allowances,
                                    'commission_rate' => $user->commission_rate,
                                    'bank_account_details' => $user->bank_account_details,
                                ]);
                        }
                    }
                });
        }

        if (Schema::hasColumn('orders', 'salesperson_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['salesperson_id']);
            });

            DB::table('orders')
                ->whereNotNull('salesperson_id')
                ->orderBy('id')
                ->chunkById(500, function ($orders) {
                    foreach ($orders as $order) {
                        $managerId = null;

                        $user = DB::table('users')
                            ->select(['manager_id', 'phone_number'])
                            ->where('id', $order->salesperson_id)
                            ->first();

                        if ($user) {
                            $managerId = $user->manager_id;

                            if (!$managerId && $user->phone_number) {
                                $managerId = DB::table('managers')
                                    ->where('phone_number', $user->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('orders')
                            ->where('id', $order->id)
                            ->update(['salesperson_id' => $managerId]);
                    }
                });

            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('salesperson_id')->references('id')->on('managers')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('hr_commissions', 'user_id')) {
            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->renameColumn('user_id', 'employee_id');
            });

            DB::table('hr_commissions')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($commissions) {
                    foreach ($commissions as $commission) {
                        $employeeId = null;

                        $user = DB::table('users')
                            ->select(['manager_id', 'phone_number'])
                            ->where('id', $commission->employee_id)
                            ->first();

                        if ($user) {
                            $employeeId = $user->manager_id;

                            if (!$employeeId && $user->phone_number) {
                                $employeeId = DB::table('managers')
                                    ->where('phone_number', $user->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_commissions')
                            ->where('id', $commission->id)
                            ->update(['employee_id' => $employeeId]);
                    }
                });

            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        $userColumnsToDrop = collect(['base_salary', 'allowances', 'commission_rate', 'bank_account_details'])
            ->filter(fn ($column) => Schema::hasColumn('users', $column))
            ->values()
            ->all();

        if (!empty($userColumnsToDrop)) {
            Schema::table('users', function (Blueprint $table) use ($userColumnsToDrop) {
                $table->dropColumn($userColumnsToDrop);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hr_advance_requests', 'employee_id')) {
            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
            });

            DB::table('hr_advance_requests')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($requests) {
                    foreach ($requests as $request) {
                        $userId = null;

                        $manager = DB::table('managers')
                            ->select(['id', 'phone_number'])
                            ->where('id', $request->employee_id)
                            ->first();

                        if ($manager) {
                            $userId = DB::table('users')
                                ->where('manager_id', $manager->id)
                                ->value('id');

                            if (!$userId && $manager->phone_number) {
                                $userId = DB::table('users')
                                    ->where('phone_number', $manager->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_advance_requests')
                            ->where('id', $request->id)
                            ->update(['employee_id' => $userId]);
                    }
                });

            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'user_id');
            });

            Schema::table('hr_advance_requests', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('hr_leave_requests', 'employee_id')) {
            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
            });

            DB::table('hr_leave_requests')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($requests) {
                    foreach ($requests as $request) {
                        $userId = null;

                        $manager = DB::table('managers')
                            ->select(['id', 'phone_number'])
                            ->where('id', $request->employee_id)
                            ->first();

                        if ($manager) {
                            $userId = DB::table('users')
                                ->where('manager_id', $manager->id)
                                ->value('id');

                            if (!$userId && $manager->phone_number) {
                                $userId = DB::table('users')
                                    ->where('phone_number', $manager->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_leave_requests')
                            ->where('id', $request->id)
                            ->update(['employee_id' => $userId]);
                    }
                });

            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'user_id');
            });

            Schema::table('hr_leave_requests', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('hr_payroll_items', 'employee_id')) {
            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
            });

            DB::table('hr_payroll_items')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($items) {
                    foreach ($items as $item) {
                        $userId = null;

                        $manager = DB::table('managers')
                            ->select(['id', 'phone_number'])
                            ->where('id', $item->employee_id)
                            ->first();

                        if ($manager) {
                            $userId = DB::table('users')
                                ->where('manager_id', $manager->id)
                                ->value('id');

                            if (!$userId && $manager->phone_number) {
                                $userId = DB::table('users')
                                    ->where('phone_number', $manager->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_payroll_items')
                            ->where('id', $item->id)
                            ->update(['employee_id' => $userId]);
                    }
                });

            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'user_id');
            });

            Schema::table('hr_payroll_items', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('managers')) {
            Schema::table('managers', function (Blueprint $table) {
                if (Schema::hasColumn('managers', 'bank_account_details')) {
                    $table->dropColumn('bank_account_details');
                }

                if (Schema::hasColumn('managers', 'commission_rate')) {
                    $table->dropColumn('commission_rate');
                }

                if (Schema::hasColumn('managers', 'allowances')) {
                    $table->dropColumn('allowances');
                }

                if (Schema::hasColumn('managers', 'base_salary')) {
                    $table->dropColumn('base_salary');
                }
            });
        }

        if (Schema::hasColumn('orders', 'salesperson_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['salesperson_id']);
            });

            DB::table('orders')
                ->whereNotNull('salesperson_id')
                ->orderBy('id')
                ->chunkById(500, function ($orders) {
                    foreach ($orders as $order) {
                        $userId = null;

                        $manager = DB::table('managers')
                            ->select(['id', 'phone_number'])
                            ->where('id', $order->salesperson_id)
                            ->first();

                        if ($manager) {
                            $userId = DB::table('users')
                                ->where('manager_id', $manager->id)
                                ->value('id');

                            if (!$userId && $manager->phone_number) {
                                $userId = DB::table('users')
                                    ->where('phone_number', $manager->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('orders')->where('id', $order->id)->update(['salesperson_id' => $userId]);
                    }
                });

            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('salesperson_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('hr_commissions', 'employee_id')) {
            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
            });

            DB::table('hr_commissions')
                ->whereNotNull('employee_id')
                ->orderBy('id')
                ->chunkById(500, function ($commissions) {
                    foreach ($commissions as $commission) {
                        $userId = null;

                        $manager = DB::table('managers')
                            ->select(['id', 'phone_number'])
                            ->where('id', $commission->employee_id)
                            ->first();

                        if ($manager) {
                            $userId = DB::table('users')
                                ->where('manager_id', $manager->id)
                                ->value('id');

                            if (!$userId && $manager->phone_number) {
                                $userId = DB::table('users')
                                    ->where('phone_number', $manager->phone_number)
                                    ->value('id');
                            }
                        }

                        DB::table('hr_commissions')
                            ->where('id', $commission->id)
                            ->update(['employee_id' => $userId]);
                    }
                });

            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'user_id');
            });

            Schema::table('hr_commissions', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'base_salary')) {
                $table->decimal('base_salary', 12, 2)->default(0)->after('wallet_balance');
            }

            if (!Schema::hasColumn('users', 'allowances')) {
                $table->decimal('allowances', 12, 2)->default(0)->after('base_salary');
            }

            if (!Schema::hasColumn('users', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 4)->default(0)->after('allowances');
            }

            if (!Schema::hasColumn('users', 'bank_account_details')) {
                $table->text('bank_account_details')->nullable()->after('commission_rate');
            }
        });
    }
};
