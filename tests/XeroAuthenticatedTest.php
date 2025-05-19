<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\XeroAuthenticated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

test('middleware redirects to connect when not connected', function () {
    // Mock the Xero facade
    Xero::shouldReceive('isConnected')->once()->andReturn(false);
    Xero::shouldReceive('connect')->once()->andReturn(new RedirectResponse('xero-connect-url'));

    // Create middleware instance
    $middleware = new XeroAuthenticated();

    // Create a request
    $request = Request::create('/test', 'GET');

    // Execute middleware
    $response = $middleware->handle($request, function () {
        return 'next middleware';
    });

    // Assert that we get a redirect response
    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('xero-connect-url');
});

test('middleware continues when connected', function () {
    // Mock the Xero facade
    Xero::shouldReceive('isConnected')->once()->andReturn(true);

    // Create middleware instance
    $middleware = new XeroAuthenticated();

    // Create a request
    $request = Request::create('/test', 'GET');

    // Execute middleware
    $response = $middleware->handle($request, function () {
        return 'next middleware';
    });

    // Assert that we continue to the next middleware
    expect($response)->toBe('next middleware');
});
