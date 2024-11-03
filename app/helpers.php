<?php

if (!function_exists('money')) {
    function money($amount, $currency = 'USD', $locale = 'en_US'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount ?? 0, $currency);
    }
}
