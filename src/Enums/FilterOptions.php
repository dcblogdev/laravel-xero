<?php

namespace Dcblogdev\Xero\Enums;

enum FilterOptions: string
{
    case IDS = 'ids';
    case INCLUDEARCHIVED = 'includeArchived';
    case ORDER = 'order';
    case PAGE = 'page';
    case SEARCHTERM = 'searchTerm';
    case SUMMARYONLY = 'summaryOnly';
    case WHERE = 'where';
    case STATUSES = 'Statuses';

    public static function isValid(string $value): bool
    {
        $validValues = array_map(fn ($case) => $case->value, self::cases());

        return in_array($value, $validValues);
    }
}
