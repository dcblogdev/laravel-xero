<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum InvoiceStatus: string
{
    case Authorised = 'AUTHORISED';
    case Deleted = 'DELETED';
    case Draft = 'DRAFT';
    case Paid = 'PAID';
    case Submitted = 'SUBMITTED';
    case Voided = 'VOIDED';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
