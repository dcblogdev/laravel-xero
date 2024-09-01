<?php

use Dcblogdev\Xero\Enums\FilterOptions;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

test('a valid option returns true', function () {
    assertTrue(FilterOptions::isValid('ids'));
    assertTrue(FilterOptions::isValid('includeArchived'));
    assertTrue(FilterOptions::isValid('order'));
    assertTrue(FilterOptions::isValid('page'));
    assertTrue(FilterOptions::isValid('searchTerm'));
    assertTrue(FilterOptions::isValid('summaryOnly'));
    assertTrue(FilterOptions::isValid('where'));
});

test('an invalid option returns false', function () {
    assertFalse(FilterOptions::isValid('bogus'));
});
