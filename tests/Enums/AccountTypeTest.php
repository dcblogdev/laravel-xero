<?php

declare(strict_types=1);

use Dcblogdev\Xero\Enums\AccountType;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(AccountType::isValid('BANK'));
    assertTrue(AccountType::isValid('CURRENT'));
    assertTrue(AccountType::isValid('CURRLIAB'));
    assertTrue(AccountType::isValid('DEPRECIATN'));
    assertTrue(AccountType::isValid('DIRECTCOSTS'));
    assertTrue(AccountType::isValid('EQUITY'));
    assertTrue(AccountType::isValid('EXPENSE'));
    assertTrue(AccountType::isValid('FIXED'));
    assertTrue(AccountType::isValid('INVENTORY'));
    assertTrue(AccountType::isValid('LIABILITY'));
    assertTrue(AccountType::isValid('NONCURRENT'));
    assertTrue(AccountType::isValid('OTHERINCOME'));
    assertTrue(AccountType::isValid('OVERHEADS'));
    assertTrue(AccountType::isValid('PREPAYMENT'));
    assertTrue(AccountType::isValid('REVENUE'));
    assertTrue(AccountType::isValid('SALES'));
    assertTrue(AccountType::isValid('TERMLIAB'));
});

test('an invalid option returns false', function () {
    assertFalse(AccountType::isValid('bogus'));
});
