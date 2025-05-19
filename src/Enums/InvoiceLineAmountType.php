<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum InvoiceLineAmountType: string
{
    case Exclusive = 'Exclusive';
    case Inclusive = 'Inclusive';
    case NoTax = 'NoTax';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
