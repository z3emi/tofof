<?php

use App\Models\Manager;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('managers')) {
            return;
        }

        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone_number')->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->foreignId('manager_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $governorateTableExists = Schema::hasTable('user_governorates');
        $persistentLoginsExists = Schema::hasTable('persistent_logins');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        if ($persistentLoginsExists) {
            Schema::table('persistent_logins', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($governorateTableExists) {
            Schema::table('user_governorates', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        DB::transaction(function () use ($governorateTableExists, $persistentLoginsExists) {
            $adminUsers = DB::table('users')->where('type', 'admin')->get();
            $idMap = [];

            foreach ($adminUsers as $admin) {
                $managerId = DB::table('managers')->insertGetId([
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone_number' => $admin->phone_number,
                    'phone_verified_at' => $admin->phone_verified_at,
                    'banned_at' => $admin->banned_at,
                    'password' => $admin->password,
                    'avatar' => $admin->avatar ?? null,
                    'manager_id' => $admin->manager_id,
                    'remember_token' => $admin->remember_token,
                    'created_at' => $admin->created_at,
                    'updated_at' => $admin->updated_at,
                ]);

                $idMap[$admin->id] = $managerId;
            }

            if (!empty($idMap)) {
                foreach ($idMap as $oldId => $newId) {
                    $oldManagerId = DB::table('managers')->where('id', $newId)->value('manager_id');
                    if ($oldManagerId !== null) {
                        $newManagerId = $idMap[$oldManagerId] ?? null;
                        DB::table('managers')->where('id', $newId)->update(['manager_id' => $newManagerId]);
                    }
                }

                $oldIds = array_keys($idMap);

                foreach ($idMap as $oldId => $newId) {
                    DB::table('orders')->where('user_id', $oldId)->update(['user_id' => $newId]);
                    DB::table('activity_logs')->where('user_id', $oldId)->update(['user_id' => $newId]);

                    if ($persistentLoginsExists) {
                        DB::table('persistent_logins')->where('user_id', $oldId)->update(['user_id' => $newId]);
                    }

                    if ($governorateTableExists) {
                        $assignments = DB::table('user_governorates')->where('user_id', $oldId)->get();
                        foreach ($assignments as $assignment) {
                            DB::table('user_governorates')->insert([
                                'user_id' => $newId,
                                'governorate' => $assignment->governorate,
                                'created_at' => $assignment->created_at,
                                'updated_at' => $assignment->updated_at,
                            ]);
                        }
                        DB::table('user_governorates')->where('user_id', $oldId)->delete();
                    }
                }

                $roleRows = DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->whereIn('model_id', $oldIds)
                    ->get();

                foreach ($roleRows as $row) {
                    DB::table('model_has_roles')->updateOrInsert(
                        [
                            'role_id' => $row->role_id,
                            'model_type' => Manager::class,
                            'model_id' => $idMap[$row->model_id],
                        ],
                        []
                    );
                }

                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->whereIn('model_id', $oldIds)
                    ->delete();

                $permissionRows = DB::table('model_has_permissions')
                    ->where('model_type', User::class)
                    ->whereIn('model_id', $oldIds)
                    ->get();

                foreach ($permissionRows as $row) {
                    DB::table('model_has_permissions')->updateOrInsert(
                        [
                            'permission_id' => $row->permission_id,
                            'model_type' => Manager::class,
                            'model_id' => $idMap[$row->model_id],
                        ],
                        []
                    );
                }

                DB::table('model_has_permissions')
                    ->where('model_type', User::class)
                    ->whereIn('model_id', $oldIds)
                    ->delete();
            }

            DB::table('users')->where('type', 'admin')->delete();
        });

        DB::table('permissions')->update(['guard_name' => 'admin']);
        DB::table('roles')->where('name', '!=', 'user')->update(['guard_name' => 'admin']);
        DB::table('roles')->where('name', 'user')->update(['guard_name' => 'web']);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('managers')->cascadeOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('managers')->nullOnDelete();
        });

        if (Schema::hasColumn('users', 'manager_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
            });
        }

        if ($persistentLoginsExists) {
            Schema::table('persistent_logins', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        if ($governorateTableExists) {
            Schema::table('user_governorates', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('managers')->cascadeOnDelete();
            });
        }

        Schema::table('managers', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $governorateTableExists = Schema::hasTable('user_governorates');
        $persistentLoginsExists = Schema::hasTable('persistent_logins');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        if (Schema::hasColumn('users', 'manager_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
            });
        }

        if ($persistentLoginsExists) {
            Schema::table('persistent_logins', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($governorateTableExists) {
            Schema::table('user_governorates', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        Schema::table('managers', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        DB::transaction(function () use ($governorateTableExists, $persistentLoginsExists) {
            $managers = DB::table('managers')->get();
            $idMap = [];

            foreach ($managers as $manager) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'phone_number' => $manager->phone_number,
                    'type' => 'admin',
                    'banned_at' => $manager->banned_at,
                    'phone_verified_at' => $manager->phone_verified_at,
                    'password' => $manager->password,
                    'remember_token' => $manager->remember_token,
                    'created_at' => $manager->created_at,
                    'updated_at' => $manager->updated_at,
                ]);

                $idMap[$manager->id] = $userId;
            }

            if (!empty($idMap)) {
                foreach ($idMap as $managerId => $userId) {
                    $oldParentId = DB::table('managers')->where('id', $managerId)->value('manager_id');
                    $newParentId = $oldParentId ? ($idMap[$oldParentId] ?? null) : null;
                    DB::table('users')->where('id', $userId)->update(['manager_id' => $newParentId]);
                }

                $managerIds = array_keys($idMap);

                foreach ($idMap as $oldId => $newId) {
                    DB::table('orders')->where('user_id', $oldId)->update(['user_id' => $newId]);
                    DB::table('activity_logs')->where('user_id', $oldId)->update(['user_id' => $newId]);

                    if ($persistentLoginsExists) {
                        DB::table('persistent_logins')->where('user_id', $oldId)->update(['user_id' => $newId]);
                    }

                    if ($governorateTableExists) {
                        $assignments = DB::table('user_governorates')->where('user_id', $oldId)->get();
                        foreach ($assignments as $assignment) {
                            DB::table('user_governorates')->insert([
                                'user_id' => $newId,
                                'governorate' => $assignment->governorate,
                                'created_at' => $assignment->created_at,
                                'updated_at' => $assignment->updated_at,
                            ]);
                        }
                        DB::table('user_governorates')->where('user_id', $oldId)->delete();
                    }
                }

                $roleRows = DB::table('model_has_roles')
                    ->where('model_type', Manager::class)
                    ->whereIn('model_id', $managerIds)
                    ->get();

                foreach ($roleRows as $row) {
                    DB::table('model_has_roles')->updateOrInsert(
                        [
                            'role_id' => $row->role_id,
                            'model_type' => User::class,
                            'model_id' => $idMap[$row->model_id],
                        ],
                        []
                    );
                }

                DB::table('model_has_roles')
                    ->where('model_type', Manager::class)
                    ->whereIn('model_id', $managerIds)
                    ->delete();

                $permissionRows = DB::table('model_has_permissions')
                    ->where('model_type', Manager::class)
                    ->whereIn('model_id', $managerIds)
                    ->get();

                foreach ($permissionRows as $row) {
                    DB::table('model_has_permissions')->updateOrInsert(
                        [
                            'permission_id' => $row->permission_id,
                            'model_type' => User::class,
                            'model_id' => $idMap[$row->model_id],
                        ],
                        []
                    );
                }

                DB::table('model_has_permissions')
                    ->where('model_type', Manager::class)
                    ->whereIn('model_id', $managerIds)
                    ->delete();
            }
        });

        DB::table('permissions')->update(['guard_name' => 'web']);
        DB::table('roles')->update(['guard_name' => 'web']);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        if (Schema::hasTable('persistent_logins')) {
            Schema::table('persistent_logins', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if ($governorateTableExists) {
            Schema::table('user_governorates', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('managers');
    }
};
