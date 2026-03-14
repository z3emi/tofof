<?php

namespace App\Models\Concerns;

use RuntimeException;

if (trait_exists(\Laravel\Sanctum\HasApiTokens::class)) {
    trait InteractsWithSanctumApiTokens
    {
        use \Laravel\Sanctum\HasApiTokens;
    }
} else {
    trait InteractsWithSanctumApiTokens
    {
        protected function sanctumNotInstalled(): never
        {
            throw new RuntimeException(
                'Laravel Sanctum package is not installed. Run "composer install" or "composer require laravel/sanctum" to enable API token features.'
            );
        }

        public function currentAccessToken()
        {
            return null;
        }

        public function tokens()
        {
            $this->sanctumNotInstalled();
        }

        public function createToken(string $name, array $abilities = ['*'])
        {
            $this->sanctumNotInstalled();
        }

        public function withAccessToken($token)
        {
            $this->sanctumNotInstalled();
        }
    }
}
