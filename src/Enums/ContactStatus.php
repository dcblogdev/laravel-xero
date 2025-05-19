<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum ContactStatus: string
{
    case Active = 'ACTIVE';
    case Archived = 'ARCHIVED';
    case GDPRRequest = 'GDPRREQUEST';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
