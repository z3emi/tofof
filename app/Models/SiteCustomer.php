<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SiteCustomer extends Customer
{
    protected $attributes = [
        'origin' => self::ORIGIN_WEBSITE,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('website-origin', function (Builder $builder) {
            $builder->where('origin', self::ORIGIN_WEBSITE);
        });

        static::creating(function (Customer $customer) {
            if (!$customer->origin) {
                $customer->origin = self::ORIGIN_WEBSITE;
            }
        });
    }
}
