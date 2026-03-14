<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Throwable;

class GoogleDriveConfig
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $baseConfig = Config::get('filesystems.disks.google', []);
        if (!is_array($baseConfig)) {
            $baseConfig = [];
        }

        try {
            $settings = Setting::whereIn('key', [
                'backup_google_drive_client_id',
                'backup_google_drive_client_secret',
                'backup_google_drive_refresh_token',
                'backup_google_drive_folder',
                'backup_google_drive_team_drive_id',
                'backup_google_drive_shared_folder_id',
            ])->pluck('value', 'key');
        } catch (Throwable $exception) {
            return self::$cache = $baseConfig;
        }

        $mapping = [
            'backup_google_drive_client_id' => 'clientId',
            'backup_google_drive_client_secret' => 'clientSecret',
            'backup_google_drive_refresh_token' => 'refreshToken',
            'backup_google_drive_folder' => 'folder',
            'backup_google_drive_team_drive_id' => 'teamDriveId',
            'backup_google_drive_shared_folder_id' => 'sharedFolderId',
        ];

        foreach ($mapping as $settingKey => $configKey) {
            $value = trim((string) ($settings[$settingKey] ?? ''));
            if ($value !== '') {
                if ($settingKey === 'backup_google_drive_folder' && str_contains($value, 'drive.google.com')) {
                    $extracted = self::extractGoogleIdFromUrl($value);
                    if ($extracted !== null) {
                        $value = $extracted;
                    }
                }

                $baseConfig[$configKey] = $value;
            }
        }

        return self::$cache = $baseConfig;
    }

    public static function hasRequiredCredentials(): bool
    {
        $config = self::get();

        foreach (['clientId', 'clientSecret', 'refreshToken'] as $requiredKey) {
            if (empty($config[$requiredKey])) {
                return false;
            }
        }

        return true;
    }

    public static function refresh(): array
    {
        self::$cache = null;

        return self::get();
    }

    private static function extractGoogleIdFromUrl(string $value): ?string
    {
        if (preg_match('~[-_a-zA-Z0-9]{10,}~', $value, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
