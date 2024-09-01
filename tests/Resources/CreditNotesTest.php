<?php

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Resources\CreditNotes;

test('invalid filter option throws exception', function () {
    Xero::creditnotes()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new CreditNotes)->filter('ids', '1234');

    expect($filter)->toBeObject();
});
