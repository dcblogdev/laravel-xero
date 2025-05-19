<?php

declare(strict_types=1);

use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Carbon;

test('xero token factory creates a valid model', function () {
    $token = XeroToken::factory()->create();

    expect($token)->toBeInstanceOf(XeroToken::class)
        ->and($token->tenant_id)->not->toBeEmpty()
        ->and($token->tenant_name)->not->toBeEmpty()
        ->and($token->access_token)->not->toBeEmpty()
        ->and($token->refresh_token)->not->toBeEmpty()
        ->and($token->expires_in)->toBeInt();
});

test('xero token can be created with mass assignment', function () {
    $data = [
        'tenant_id' => 'test_tenant_id',
        'tenant_name' => 'Test Tenant',
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'expires_in' => 3600,
        'scopes' => 'test_scope',
    ];

    $token = XeroToken::create($data);

    expect($token)->toBeInstanceOf(XeroToken::class)
        ->and($token->tenant_id)->toBe('test_tenant_id')
        ->and($token->tenant_name)->toBe('Test Tenant')
        ->and($token->access_token)->toBe('test_access_token')
        ->and($token->refresh_token)->toBe('test_refresh_token')
        ->and($token->expires_in)->toBe(3600)
        ->and($token->scopes)->toBe('test_scope');
});

test('expires attribute returns correct expiration time', function () {
    // Freeze time for predictable testing
    Carbon::setTestNow('2023-01-01 12:00:00');

    $token = XeroToken::create([
        'tenant_id' => 'test_tenant_id',
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'expires_in' => 3600, // 1 hour
        'scopes' => 'test_scope',
    ]);

    // The expires attribute should be 1 hour after the updated_at timestamp
    expect($token->expires->format('Y-m-d H:i:s'))->toBe('2023-01-01 13:00:00');

    // Clean up
    Carbon::setTestNow();
});

test('expires_in is cast to integer', function () {
    $token = XeroToken::create([
        'tenant_id' => 'test_tenant_id',
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'expires_in' => '3600', // String value
        'scopes' => 'test_scope',
    ]);

    expect($token->expires_in)->toBeInt()
        ->and($token->expires_in)->toBe(3600);
});
