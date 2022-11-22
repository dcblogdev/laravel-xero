<?php

use Dcblogdev\Xero\Facades\Xero as XeroFacade;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Xero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

beforeEach(function () {
    $this->XeroMock = Mockery::mock(Xero::class);
});

test('can initalise', function () {
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