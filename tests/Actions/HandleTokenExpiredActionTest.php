<?php

use Dcblogdev\Xero\Actions\tokenExpiredAction;
use Dcblogdev\Xero\Models\XeroToken;

use function Pest\Laravel\assertDatabaseCount;

test('token refresh throws exception when expired and a refresh is attempted over cli', function () {

    $token = XeroToken::factory()->create();

    $result = ['error' => 'invalid_grant'];

    app(tokenExpiredAction::class)($result, $token);

    assertDatabaseCount(XeroToken::class, 0);

})->throws(Exception::class, 'Xero token has expired, please re-authenticate.');

test('token refresh does not throw an exception and token is not deleted', function () {

    $token = XeroToken::factory()->create();

    $result = [];

    $response = app(tokenExpiredAction::class)($result, $token);

    expect($response)->toBeNull();

    assertDatabaseCount(XeroToken::class, 1);
});
