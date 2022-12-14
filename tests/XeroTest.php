<?php

use Dcblogdev\Xero\Facades\Xero as XeroFacade;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Xero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

beforeEach(function () {
    $this->XeroMock = Mockery::mock(Xero::class);
});

test('can initialise', function () {
    $this->assertInstanceOf(Xero::class, $this->XeroMock);
});

test('redirected when connect is called', function () {
    $connect = XeroFacade::connect();

    $this->assertInstanceOf(RedirectResponse::class, $connect);
});

test('is connected returns false when no data in db', function () {
    $connect = XeroFacade::isConnected();

    expect($connect)->toBeFalse();
});

test('is connected returns true when data exists in db', function () {

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts'
    ]);

    $connect = XeroFacade::isConnected();

    expect($connect)->toBeTrue();
});

test('disconnect returns true when data exists in db', function () {

    Http::fake();

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts'
    ]);

    XeroFacade::disconnect();

    $this->assertDatabaseCount('xero_tokens', 0);
});

test('getTokenData returns XeroToken data', function () {

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts'
    ]);

    $data = XeroFacade::getTokenData();

    expect($data)->toBeObject();
});

test('getTokenData when no tokens exist returns null', function () {
    expect(XeroFacade::getTokenData())->toBeNull;
});

test('getTenantId returns id', function () {
    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts',
        'tenant_id' => '1234'
    ]);

   expect(XeroFacade::getTenantId())->toBe('1234');
});

test('getTenantName returns name', function () {
    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts',
        'tenant_id' => '1234',
        'tenant_name' => 'Jones'
    ]);

   expect(XeroFacade::getTenantName())->toBe('Jones');
});

test('can return getAccessToken when it has not expired ', function () {

    Http::fake();

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(15),
        'scopes' => 'contacts'
    ]);

    $data = XeroFacade::getAccessToken();

    expect($data)->toBe('1234');
});