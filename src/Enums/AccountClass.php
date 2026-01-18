<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum AccountClass: string
{
    case Asset = 'ASSET';
    case Equity = 'EQUITY';
    case Expense = 'EXPENSE';
    case Liability = 'LIABILITY';
    case Revenue = 'REVENUE';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
