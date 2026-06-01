<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class Currency
{
    /**
     * Supported currencies with their symbol and XOF rate (1 unit = X XOF).
     * Rates are approximations — replace with live API in production.
     */
    public static array $currencies = [
        'XOF' => ['name' => 'Franc CFA UEMOA', 'symbol' => 'FCFA', 'rate' => 1,          'decimals' => 0, 'flag' => '🌍'],
        'XAF' => ['name' => 'Franc CFA CEMAC', 'symbol' => 'FCFA', 'rate' => 1,          'decimals' => 0, 'flag' => '🌍'],
        'NGN' => ['name' => 'Naira nigérian',  'symbol' => '₦',    'rate' => 0.49,       'decimals' => 0, 'flag' => '🇳🇬'],
        'GHS' => ['name' => 'Cedi ghanéen',    'symbol' => 'GH₵',  'rate' => 3.20,       'decimals' => 2, 'flag' => '🇬🇭'],
        'USD' => ['name' => 'Dollar américain','symbol' => '$',     'rate' => 620.00,     'decimals' => 2, 'flag' => '🇺🇸'],
        'EUR' => ['name' => 'Euro',            'symbol' => '€',     'rate' => 655.96,     'decimals' => 2, 'flag' => '🇪🇺'],
        'GBP' => ['name' => 'Livre sterling',  'symbol' => '£',     'rate' => 780.00,     'decimals' => 2, 'flag' => '🇬🇧'],
    ];

    /**
     * Convert an XOF amount to the target currency.
     */
    public static function fromXOF(float $xofAmount, string $currency): float
    {
        $rate = self::$currencies[$currency]['rate'] ?? 1;
        if ($rate <= 0) return $xofAmount;
        return $xofAmount / $rate;
    }

    /**
     * Convert an amount in $currency to XOF.
     */
    public static function toXOF(float $amount, string $currency): float
    {
        $rate = self::$currencies[$currency]['rate'] ?? 1;
        return $amount * $rate;
    }

    /**
     * Format an XOF amount in the given (or user's preferred) currency.
     */
    public static function format(float $xofAmount, ?string $currency = null): string
    {
        $currency ??= self::userCurrency();
        $info      = self::$currencies[$currency] ?? self::$currencies['XOF'];
        $converted = self::fromXOF($xofAmount, $currency);

        return number_format($converted, $info['decimals'], ',', ' ') . ' ' . $info['symbol'];
    }

    /**
     * Get the authenticated user's preferred currency (or default).
     */
    public static function userCurrency(): string
    {
        if (Auth::check()) {
            return Auth::user()->currency ?? 'XOF';
        }
        return session('currency', 'XOF');
    }

    /**
     * Get the symbol for a currency code.
     */
    public static function symbol(string $currency): string
    {
        return self::$currencies[$currency]['symbol'] ?? $currency;
    }

    /**
     * List of currencies suitable for <select> options.
     */
    public static function options(): array
    {
        return collect(self::$currencies)->map(fn($c, $code) => [
            'code'   => $code,
            'label'  => $c['flag'] . ' ' . $c['name'] . ' (' . $c['symbol'] . ')',
            'symbol' => $c['symbol'],
        ])->values()->toArray();
    }
}
