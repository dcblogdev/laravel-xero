<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Traits;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Dcblogdev\Xero\Actions\formatQueryStringsAction;
use Throwable;

trait XeroHelpersTrait
{
    /**
     * Parse a date string into a formatted date.
     */
    public static function formatDate(string $date, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            // Match a Microsoft JSON date format: /Date(1663257600000+0100)/
            if (preg_match('#^/Date\((\d+)([+-]\d{4})\)/$#', $date, $matches)) {
                $timestamp = (int) $matches[1] / 1000;
                $offset = $matches[2];
                $tzOffset = mb_substr($offset, 0, 3).':'.mb_substr($offset, 3);

                $dt = new DateTimeImmutable("@$timestamp");
                $dt->setTimezone(new DateTimeZone($tzOffset));

                return $dt->format($format);
            }

            // Fallback to default DateTime parsing
            $dt = new DateTimeImmutable($date);

            return $dt->format($format);

        } catch (Throwable $e) {
            // Invalid date input, return empty string instead of crashing
            return '';
        }
    }

    /**
     * Format query strings for API requests.
     */
    public static function formatQueryStrings(array $params): string
    {
        return app(formatQueryStringsAction::class)($params);
    }
}