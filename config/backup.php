<?php

return [

    'backup' => [

        'name' => env('APP_NAME', 'Tofof'),

        'source' => [

            'files' => [
                'include' => [], // تم إفراغها للتركيز على قاعدة البيانات فقط
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],
                'follow_links' => false,
                'relative_path' => base_path(), // ✅ تم إضافته لحل المشكلة
            ],

            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        // ===== START: التعديل المطلوب =====
        // تعطيل الضغط لإنشاء ملف .sql مباشرة
        'database_dump_compressor' => null,
        // تحديد امتداد الملف
        'database_dump_file_extension' => 'sql',
        // ===== END: التعديل المطلوب =====

        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
    ],

    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['log'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['log'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['log'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['log'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['log'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['log'],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',
        ],

        'slack' => [
            'webhook_url' => '',
            'username' => 'Laravel Backup',
            'icon' => ':robot:',
            'channel' => null,
        ],

        'discord' => [
            'webhook_url' => '',
            'username' => 'Laravel Backup',
            'avatar_url' => '',
            'channel' => null,
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];
