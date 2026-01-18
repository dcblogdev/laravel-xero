<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\AccountClass;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(AccountClass::isValid('ASSET'));
    assertTrue(AccountClass::isValid('EQUITY'));
    assertTrue(AccountClass::isValid('EXPENSE'));
    assertTrue(AccountClass::isValid('LIABILITY'));
    assertTrue(AccountClass::isValid('REVENUE'));
});

test('an invalid option returns false', function () {
    assertFalse(AccountClass::isValid('bogus'));
});
