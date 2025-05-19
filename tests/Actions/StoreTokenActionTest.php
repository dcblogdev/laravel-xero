<?php

declare(strict_types=1);

use Dcblogdev\Xero\Actions\StoreTokenAction;
use Dcblogdev\Xero\Models\XeroToken;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('store token action creates a new token without tenant data', function () {
    $token = [
        'id_token' => 'test_id_token',
        'access_token' => 'test_access_token',
        'expires_in' => 3600,
        'token_type' => 'Bearer',
        'refresh_token' => 'test_refresh_token',
        'scope' => 'test_scope',
    ];

    $result = app(StoreTokenAction::class)($token);

    expect($result)->toBeInstanceOf(XeroToken::class);
    assertDatabaseCount(XeroToken::class, 1);
    assertDatabaseHas('xero_tokens', [
        'id' => 1,
        'access_token' => config('xero.encrypt') ? null : 'test_access_token',
        'refresh_token' => config('xero.encrypt') ? null : 'test_refresh_token',
        'scopes' => 'test_scope',
    ]);
});

test('store token action creates a new token with tenant data', function () {
    $token = [
        'id_token' => 'test_id_token',
        'access_token' => 'test_access_token',
        'expires_in' => 3600,
        'token_type' => 'Bearer',
        'refresh_token' => 'test_refresh_token',
        'scope' => 'test_scope',
    ];

    $tenantData = [
        'tenant_id' => 'test_tenant_id',
        'tenant_name' => 'Test Tenant',
    ];

    $result = app(StoreTokenAction::class)($token, $tenantData);

    expect($result)->toBeInstanceOf(XeroToken::class);
    assertDatabaseCount(XeroToken::class, 1);
    assertDatabaseHas('xero_tokens', [
        'tenant_id' => 'test_tenant_id',
        'tenant_name' => 'Test Tenant',
        'access_token' => config('xero.encrypt') ? null : 'test_access_token',
        'refresh_token' => config('xero.encrypt') ? null : 'test_refresh_token',
        'scopes' => 'test_scope',
    ]);
});

test('store token action updates an existing token with tenant id and tenant data', function () {
    // First, create a token
    $token = XeroToken::factory()->create([
        'tenant_id' => 'test_tenant_id',
        'tenant_name' => 'Old Tenant Name',
    ]);

    // Then update it with new data
    $newToken = [
        'id_token' => 'new_id_token',
        'access_token' => 'new_access_token',
        'expires_in' => 7200,
        'token_type' => 'Bearer',
        'refresh_token' => 'new_refresh_token',
        'scope' => 'new_scope',
    ];

    $tenantData = [
        'tenant_name' => 'New Tenant Name',
    ];

    $result = app(StoreTokenAction::class)($newToken, $tenantData, 'test_tenant_id');

    expect($result)->toBeInstanceOf(XeroToken::class);
    assertDatabaseCount(XeroToken::class, 1);
    assertDatabaseHas('xero_tokens', [
        'tenant_id' => 'test_tenant_id',
        'tenant_name' => 'New Tenant Name',
        'access_token' => config('xero.encrypt') ? null : 'new_access_token',
        'refresh_token' => config('xero.encrypt') ? null : 'new_refresh_token',
        'scopes' => 'new_scope',
    ]);
});
