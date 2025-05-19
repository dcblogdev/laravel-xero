<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\InvoiceStatus;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(InvoiceStatus::isValid('AUTHORISED'));
    assertTrue(InvoiceStatus::isValid('DELETED'));
    assertTrue(InvoiceStatus::isValid('DRAFT'));
    assertTrue(InvoiceStatus::isValid('PAID'));
    assertTrue(InvoiceStatus::isValid('SUBMITTED'));
    assertTrue(InvoiceStatus::isValid('VOIDED'));
});

test('an invalid option returns false', function () {
    assertFalse(InvoiceStatus::isValid('bogus'));
});
