<?php

use Dcblogdev\Xero\Actions\tokenExpiredAction;
use Dcblogdev\Xero\Exceptions\XeroTokenExpiredException;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\assertDatabaseCount;

test('token refresh throws exception when expired and a refresh is attempted over cli', function(){

    $token = XeroToken::factory()->create();

    $result = ['error' => 'invalid_grant'];

    $action = new tokenExpiredAction();
    $action->handle($result, $token);

    assertDatabaseCount(XeroToken::class, 0);

})->throws(Exception::class, 'Xero token has expired, please re-authenticate.');

test('token refresh does not throw an exception and token is not deleted', function(){

    $token = XeroToken::factory()->create();

    $result = [];

    $action = new tokenExpiredAction();
    $response = $action->handle($result, $token);

    expect($response)->toBeNull();

    assertDatabaseCount(XeroToken::class, 1);

});

test('wip', function(){

    $token = XeroToken::factory()->create();

    $result = ['error' => 'invalid_grant'];

    try {
        $action = new TokenExpiredAction();
        $action->handle($result, $token);
    } catch (XeroTokenExpiredException $e) {
        $this->assertEquals(config('xero.redirectUri'), $e->render()->getTargetUrl());
    }


})->throws(XeroTokenExpiredException::class, 'Xero token has expired, please re-authenticate.');

test('token refresh redirects when expired and a refresh is attempted over HTTP', function () {

    $this->withoutExceptionHandling();

    $token = XeroToken::factory()->create();
    assertDatabaseCount(XeroToken::class, 1);

    // Define a temporary route in the test to handle the action.
    Route::post('/test-endpoint', function () use ($token) {
        $result = ['error' => 'invalid_grant'];
        $action = new TokenExpiredAction();
        $action->handle($result, $token);
    });

    $this->post('/test-endpoint')
        ->assertRedirect(config('xero.redirectUri'));

    assertDatabaseCount(XeroToken::class, 0);
})->throws(XeroTokenExpiredException::class);