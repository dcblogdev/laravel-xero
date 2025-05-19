<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\InvoiceLineAmountType;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(InvoiceLineAmountType::isValid('Exclusive'));
    assertTrue(InvoiceLineAmountType::isValid('Inclusive'));
    assertTrue(InvoiceLineAmountType::isValid('NoTax'));
});

test('an invalid option returns false', function () {
    assertFalse(InvoiceLineAmountType::isValid('bogus'));
});
