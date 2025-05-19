<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\ContactStatus;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(ContactStatus::isValid('ACTIVE'));
    assertTrue(ContactStatus::isValid('ARCHIVED'));
    assertTrue(ContactStatus::isValid('GDPRREQUEST'));
});

test('an invalid option returns false', function () {
    assertFalse(ContactStatus::isValid('bogus'));
});
