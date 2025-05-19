<?php

declare(strict_types=1);

use Dcblogdev\Xero\Console\Commands\XeroKeepAliveCommand;
use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    // Register the command
    $this->app->singleton('command.xero.keep-alive', fn () => new XeroKeepAliveCommand());
    Artisan::registerCommand($this->app->make('command.xero.keep-alive'));
});

test('command handles no tokens gracefully', function () {
    // Ensure no tokens exist
    XeroToken::query()->delete();

    // Run the command
    $this->artisan('xero:keep-alive')
        ->assertExitCode(0);
});

test('command refreshes token for connected tenant', function () {
    // Create a token
    $token = XeroToken::factory()->create([
        'tenant_name' => 'Connected Tenant',
        'tenant_id' => 'connected-tenant-id',
    ]);

    // Mock the Xero facade
    Xero::shouldReceive('setTenantId')
        ->once()
        ->with('connected-tenant-id');

    Xero::shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    Xero::shouldReceive('getAccessToken')
        ->once()
        ->with(false)
        ->andReturn('refreshed-token');

    // Run the command
    $this->artisan('xero:keep-alive')
        ->expectsOutput('Refreshing Token for Tenant: Connected Tenant - Successful')
        ->assertExitCode(0);
});

test('command handles not connected tenant', function () {
    // Create a token
    $token = XeroToken::factory()->create([
        'tenant_name' => 'Not Connected Tenant',
        'tenant_id' => 'not-connected-tenant-id',
    ]);

    // Mock the Xero facade
    Xero::shouldReceive('setTenantId')
        ->once()
        ->with('not-connected-tenant-id');

    Xero::shouldReceive('isConnected')
        ->once()
        ->andReturn(false);

    // Run the command
    $this->artisan('xero:keep-alive')
        ->expectsOutput('Refreshing Token for Tenant: Not Connected Tenant - Not Connected')
        ->assertExitCode(0);
});

test('command handles multiple tenants', function () {
    // Create multiple tokens
    $token1 = XeroToken::factory()->create([
        'tenant_name' => 'Tenant 1',
        'tenant_id' => 'tenant-id-1',
    ]);

    $token2 = XeroToken::factory()->create([
        'tenant_name' => 'Tenant 2',
        'tenant_id' => 'tenant-id-2',
    ]);

    // Mock the Xero facade for first tenant (connected)
    Xero::shouldReceive('setTenantId')
        ->once()
        ->with('tenant-id-1');

    Xero::shouldReceive('isConnected')
        ->once()
        ->andReturn(true);

    Xero::shouldReceive('getAccessToken')
        ->once()
        ->with(false)
        ->andReturn('refreshed-token-1');

    // Mock the Xero facade for second tenant (not connected)
    Xero::shouldReceive('setTenantId')
        ->once()
        ->with('tenant-id-2');

    Xero::shouldReceive('isConnected')
        ->once()
        ->andReturn(false);

    // Run the command
    $this->artisan('xero:keep-alive')
        ->expectsOutput('Refreshing Token for Tenant: Tenant 1 - Successful')
        ->expectsOutput('Refreshing Token for Tenant: Tenant 2 - Not Connected')
        ->assertExitCode(0);
});
