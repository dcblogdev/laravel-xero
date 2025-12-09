<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Resources\Contacts;
use Illuminate\Support\Facades\Http;

test('invalid filter option throws exception', function () {
    Xero::contacts()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new Contacts)->filter('ids', '1234');

    expect($filter)->toBeObject();
});

test('get returns only contacts array by default', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'Contacts' => [
                ['ContactID' => '1', 'Name' => 'Contact 1'],
                ['ContactID' => '2', 'Name' => 'Contact 2'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'contacts',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::contacts()->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0])->toHaveKey('ContactID')
        ->and($result)->not->toHaveKey('Id')
        ->and($result)->not->toHaveKey('Status');
});

test('get returns full response body when withFullResponse is called', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'Contacts' => [
                ['ContactID' => '1', 'Name' => 'Contact 1'],
                ['ContactID' => '2', 'Name' => 'Contact 2'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'contacts',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::contacts()->withFullResponse()->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('Id')
        ->and($result)->toHaveKey('Status')
        ->and($result)->toHaveKey('ProviderName')
        ->and($result)->toHaveKey('DateTimeUTC')
        ->and($result)->toHaveKey('Contacts')
        ->and($result['Id'])->toBe('test-id')
        ->and($result['Status'])->toBe('OK')
        ->and($result['Contacts'])->toBeArray()
        ->and($result['Contacts'])->toHaveCount(2);
});

test('get returns full response with pagination when withFullResponse is called', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'pagination' => [
                'page' => 1,
                'pageSize' => 100,
                'pageCount' => 1,
                'itemCount' => 2,
            ],
            'Contacts' => [
                ['ContactID' => '1', 'Name' => 'Contact 1'],
                ['ContactID' => '2', 'Name' => 'Contact 2'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'contacts',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::contacts()->withFullResponse()->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('pagination')
        ->and($result['pagination'])->toBeArray()
        ->and($result['pagination']['page'])->toBe(1)
        ->and($result['pagination']['pageSize'])->toBe(100)
        ->and($result['pagination']['itemCount'])->toBe(2);
});

test('withFullResponse can be chained with filter', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'Contacts' => [
                ['ContactID' => '1', 'Name' => 'Contact 1'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'contacts',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::contacts()
        ->filter('ids', '1')
        ->withFullResponse()
        ->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('Id')
        ->and($result)->toHaveKey('Contacts');
});

test('withFullResponse returns self for fluent chaining', function () {
    $contacts = new Contacts;

    $result = $contacts->withFullResponse();

    expect($result)->toBeInstanceOf(Contacts::class)
        ->and($result)->toBe($contacts);
});
