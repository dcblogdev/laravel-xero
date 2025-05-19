<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Enums;

enum FilterOptions: string
{
    case Ids = 'ids';
    case IncludeArchived = 'includeArchived';
    case Order = 'order';
    case Page = 'page';
    case SearchTerm = 'searchTerm';
    case SummaryOnly = 'summaryOnly';
    case Where = 'where';
    case Statuses = 'Statuses';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn (mixed $case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
