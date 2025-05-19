<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\InvoiceType;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(InvoiceType::isValid('ACCPAY'));
    assertTrue(InvoiceType::isValid('ACCREC'));
});

test('an invalid option returns false', function () {
    assertFalse(InvoiceType::isValid('bogus'));
});
