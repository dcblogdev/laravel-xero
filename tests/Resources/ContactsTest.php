<?php

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Resources\Contacts;

test('invalid filter option throws exception', function () {
    Xero::contacts()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new Contacts)->filter('ids', '1234');

    expect($filter)->toBeObject();
});
