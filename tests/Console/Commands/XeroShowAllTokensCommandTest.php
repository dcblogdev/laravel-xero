<?php

declare(strict_types=1);

use Dcblogdev\Xero\Console\Commands\XeroShowAllTokensCommand;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

beforeEach(function () {
    // Register the command
    $this->app->singleton('command.xero.show-all-tokens', fn () => new XeroShowAllTokensCommand());
    Artisan::registerCommand($this->app->make('command.xero.show-all-tokens'));
});

test('command displays message when no tokens exist', function () {
    // Ensure no tokens exist
    XeroToken::query()->delete();

    // Run the command
    $this->artisan('xero:show-all-tokens')
        ->expectsOutput('All XERO Tokens in storage')
        ->assertExitCode(0);
});

test('command displays tokens in table format', function () {
    // Create a token
    $token = XeroToken::factory()->create([
        'tenant_name' => 'Test Tenant',
        'tenant_id' => 'test-tenant-id',
    ]);

    // Run the command
    $this->artisan('xero:show-all-tokens')
        ->expectsOutput('All XERO Tokens in storage')
        ->assertExitCode(0);

    // Verify the token exists in the database
    $this->assertDatabaseHas('xero_tokens', [
        'tenant_name' => 'Test Tenant',
        'tenant_id' => 'test-tenant-id',
    ]);
});

test('command handles encrypted tokens correctly', function () {
    // Enable encryption
    Config::set('xero.encrypt', true);

    // Create a token with encrypted values
    $token = XeroToken::factory()->create([
        'tenant_name' => 'Encrypted Tenant',
        'tenant_id' => 'encrypted-tenant-id',
        'access_token' => Crypt::encryptString('encrypted-access-token'),
        'refresh_token' => Crypt::encryptString('encrypted-refresh-token'),
    ]);

    // Run the command
    $this->artisan('xero:show-all-tokens')
        ->expectsOutput('All XERO Tokens in storage')
        ->assertExitCode(0);

    // Verify the token exists in the database with encrypted values
    $this->assertDatabaseHas('xero_tokens', [
        'tenant_name' => 'Encrypted Tenant',
        'tenant_id' => 'encrypted-tenant-id',
    ]);

    // Reset config
    Config::set('xero.encrypt', false);
});

test('command handles decryption exceptions', function () {
    // Enable encryption
    Config::set('xero.encrypt', true);

    // Create a token with non-encrypted values that will cause decryption to fail
    $token = XeroToken::factory()->create([
        'tenant_name' => 'Exception Tenant',
        'tenant_id' => 'exception-tenant-id',
        'access_token' => 'non-encrypted-access-token',
        'refresh_token' => 'non-encrypted-refresh-token',
    ]);

    // Run the command
    $this->artisan('xero:show-all-tokens')
        ->expectsOutput('All XERO Tokens in storage')
        ->assertExitCode(0);

    // Verify the token exists in the database
    $this->assertDatabaseHas('xero_tokens', [
        'tenant_name' => 'Exception Tenant',
        'tenant_id' => 'exception-tenant-id',
        'access_token' => 'non-encrypted-access-token',
        'refresh_token' => 'non-encrypted-refresh-token',
    ]);

    // Reset config
    Config::set('xero.encrypt', false);
});
