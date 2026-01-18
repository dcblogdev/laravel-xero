<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Resources\Accounts;

test('invalid filter option throws exception', function () {
    Xero::accounts()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new Accounts)->filter('ids', '1234');

    expect($filter)->toBeObject();
});
