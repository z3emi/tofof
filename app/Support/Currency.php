<?php

namespace App\Support;

use App\Models\Setting;

class Currency
{
    public const CONTEXT_SYSTEM = 'system';
    public const CONTEXT_SITE = 'site';
    public const IQD = 'IQD';
    public const USD = 'USD';

    public static function normalize(string $currency): string
    {
        return strtoupper(trim($currency));
    }

    public static function siteCurrency(): string
    {
        return self::IQD;
    }

    public static function siteSymbol(): string
    {
        return 'د.ع';
    }

    public static function systemCurrency(): string
    {
        $default = self::IQD;

        try {
            $value = Setting::getValue('system_currency', $default);
            $value = strtoupper((string) $value);

            return in_array($value, [self::IQD, self::USD], true) ? $value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function iqdToUsdRate(): float
    {
        $default = 1300.0;

        try {
            $value = (float) Setting::getValue('iqd_usd_exchange_rate', $default);
        } catch (\Throwable $e) {
            $value = $default;
        }

        return $value > 0 ? $value : $default;
    }

    public static function systemSymbol(): string
    {
        return self::symbol(self::CONTEXT_SYSTEM);
    }

    public static function symbol(string $context = self::CONTEXT_SYSTEM): string
    {
        if ($context === self::CONTEXT_SITE) {
            return self::siteSymbol();
        }

        return self::symbolFor(self::systemCurrency());
    }

    public static function format(float|int|string|null $amount, string $context = self::CONTEXT_SYSTEM, ?int $precision = null, bool $withSymbol = true): string
    {
        $numericAmount = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);

        if ($context === self::CONTEXT_SYSTEM) {
            [$numericAmount, $precision] = self::normalizeForSystem($numericAmount, $precision);
        } else {
            if ($precision === null) {
                $precision = 0;
            }
        }

        $formatted = number_format($numericAmount, max(0, $precision));

        if (!$withSymbol) {
            return $formatted;
        }

        $symbol = self::symbol($context);
        $symbolPosition = $context === self::CONTEXT_SYSTEM && self::systemCurrency() === self::USD ? 'prefix' : 'suffix';

        return $symbolPosition === 'prefix'
            ? $symbol . ' ' . $formatted
            : $formatted . ' ' . $symbol;
    }

    public static function convertToSystem(float|int|string|null $amount, string $sourceCurrency, ?float $exchangeRate = null): float
    {
        $value = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);

        return self::convert($value, $sourceCurrency, self::systemCurrency(), $exchangeRate);
    }

    public static function convertFromSystem(float|int|string|null $amount, string $targetCurrency, ?float $exchangeRate = null): float
    {
        $value = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);

        return self::convert($value, self::systemCurrency(), $targetCurrency, $exchangeRate);
    }

    public static function convert(float|int|string|null $amount, string $fromCurrency, string $toCurrency, ?float $exchangeRate = null): float
    {
        $value = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);

        $from = self::normalize($fromCurrency);
        $to = self::normalize($toCurrency);

        if ($from === $to) {
            return $value;
        }

        $rate = $exchangeRate !== null && $exchangeRate > 0 ? $exchangeRate : self::iqdToUsdRate();

        if ($from === self::USD && $to === self::IQD) {
            return $value * $rate;
        }

        if ($from === self::IQD && $to === self::USD) {
            return $value / $rate;
        }

        return $value;
    }

    public static function formatForCurrency(float|int|string|null $amount, string $currency, ?int $precision = null, bool $withSymbol = true): string
    {
        $value = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);
        $currency = self::normalize($currency);

        if ($precision === null) {
            $precision = self::precisionFor($currency);
        }

        $formatted = number_format($value, max(0, $precision));

        if (!$withSymbol) {
            return $formatted;
        }

        $symbol = self::symbolFor($currency);

        return $currency === self::USD
            ? $symbol . ' ' . $formatted
            : $formatted . ' ' . $symbol;
    }

    public static function symbolFor(string $currency): string
    {
        return self::normalize($currency) === self::USD ? '$' : 'د.ع';
    }

    public static function precisionFor(string $currency): int
    {
        return self::normalize($currency) === self::USD ? 2 : 0;
    }

    public static function roundForCurrency(float|int|string|null $amount, string $currency): float
    {
        $value = is_string($amount) ? (float) $amount : (float) ($amount ?? 0);

        return round($value, self::precisionFor($currency));
    }

    protected static function normalizeForSystem(float $value, ?int $precision): array
    {
        $currency = self::systemCurrency();

        if ($currency === self::USD) {
            $value = $value / self::iqdToUsdRate();
            $precision = $precision ?? 2;
        } else {
            $precision = $precision ?? 0;
        }

        return [$value, $precision];
    }
}
