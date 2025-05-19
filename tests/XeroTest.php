<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero as XeroFacade;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Xero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

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
        'scopes' => 'contacts',
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
        'scopes' => 'contacts',
    ]);

    XeroFacade::disconnect();

    $this->assertDatabaseCount('xero_tokens', 0);
});

test('getTokenData returns XeroToken data', function () {

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts',
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
        'tenant_id' => '1234',
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
        'tenant_name' => 'Jones',
    ]);

    expect(XeroFacade::getTenantName())->toBe('Jones');
});

test('can return getAccessToken when it has not expired ', function () {

    Http::fake();

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'updated_at' => strtotime('+1 day'),
        'scopes' => 'contacts',
    ]);

    $data = XeroFacade::getAccessToken();

    expect($data)->toBe('1234');
});

test('can get tokens when not-encrypted but encryption is enabled', function () {

    Config::set('xero.encrypt', true);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts',
    ]);

    $data = XeroFacade::getAccessToken();

    expect($data)->toBe('1234');
});

test('can get tokens when encrypted', function () {

    Config::set('xero.encrypt', true);

    XeroToken::create([
        'id' => 0,
        'access_token' => Crypt::encryptString('1234'),
        'expires_in' => strtotime('+1 day'),
        'scopes' => 'contacts',
    ]);

    $data = XeroFacade::getAccessToken();

    expect($data)->toBe('1234');
});

test('formats Microsoft JSON date with timezone offset', function () {
    $input = '/Date(1663257600000+0100)/';
    $formatted = Xero::formatDate($input);

    expect($formatted)->toBe('2022-09-15 16:00:00');
});

test('formats standard ISO 8601 date string', function () {
    $input = '2023-05-19T14:00:00+00:00';
    $formatted = Xero::formatDate($input);

    expect($formatted)->toBe('2023-05-19 14:00:00');
});

test('returns empty string for invalid date input', function () {
    $input = 'invalid-date-format';
    $formatted = Xero::formatDate($input);

    expect($formatted)->toBe('');
});

test('formats Microsoft JSON date with UTC offset', function () {
    $input = '/Date(1663257600000+0000)/';
    $formatted = Xero::formatDate($input);

    expect($formatted)->toBe('2022-09-15 16:00:00');
});
