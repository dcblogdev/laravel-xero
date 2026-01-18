<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\AccountStatus;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(AccountStatus::isValid('ACTIVE'));
    assertTrue(AccountStatus::isValid('ARCHIVED'));
});

test('an invalid option returns false', function () {
    assertFalse(AccountStatus::isValid('bogus'));
});
