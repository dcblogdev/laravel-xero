<?php

declare(strict_types=1);

use Dcblogdev\Xero\Actions\tokenExpiredAction;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;

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

test('token refresh redirects when expired and not running in console', function () {
    // Create a test-specific version of the tokenExpiredAction class
    // that overrides isRunningInConsole to return false
    $testAction = new class extends tokenExpiredAction
    {
        protected function isRunningInConsole(): bool
        {
            return false;
        }
    };

    // Set a test redirect URI
    Config::set('xero.redirectUri', 'https://example.com/auth');

    $token = XeroToken::factory()->create();

    $result = ['error' => 'invalid_grant'];

    $response = $testAction($result, $token);

    // Assert token was deleted
    assertDatabaseCount(XeroToken::class, 0);

    // Assert response is a redirect to the configured URI
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe('https://example.com/auth');
});
