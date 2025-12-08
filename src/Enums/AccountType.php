<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum AccountType: string
{
    case Bank = 'BANK';
    case Current = 'CURRENT';
    case Currliab = 'CURRLIAB';
    case Depreciatn = 'DEPRECIATN';
    case Directcosts = 'DIRECTCOSTS';
    case Equity = 'EQUITY';
    case Expense = 'EXPENSE';
    case Fixed = 'FIXED';
    case Inventory = 'INVENTORY';
    case Liability = 'LIABILITY';
    case Noncurrent = 'NONCURRENT';
    case Otherincome = 'OTHERINCOME';
    case Overheads = 'OVERHEADS';
    case Prepayment = 'PREPAYMENT';
    case Revenue = 'REVENUE';
    case Sales = 'SALES';
    case Termliab = 'TERMLIAB';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
