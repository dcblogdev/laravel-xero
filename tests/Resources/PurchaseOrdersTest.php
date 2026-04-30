<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Resources\PurchaseOrders;
use Illuminate\Support\Facades\Http;

test('invalid filter option throws exception', function () {
    Xero::purchaseorders()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new PurchaseOrders)->filter('ids', '1234');

    expect($filter)->toBeObject();
});

test('get returns only purchase orders array by default', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/PurchaseOrders*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'PurchaseOrders' => [
                ['PurchaseOrderID' => '1', 'PurchaseOrderNumber' => 'PO-001'],
                ['PurchaseOrderID' => '2', 'PurchaseOrderNumber' => 'PO-002'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::purchaseorders()->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0])->toHaveKey('PurchaseOrderID')
        ->and($result)->not->toHaveKey('Id')
        ->and($result)->not->toHaveKey('Status');
});

test('get returns full response body when withFullResponse is called', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/PurchaseOrders*' => Http::response([
            'Id' => 'test-id',
            'Status' => 'OK',
            'ProviderName' => 'Test Provider',
            'DateTimeUTC' => '/Date(1234567890)/',
            'PurchaseOrders' => [
                ['PurchaseOrderID' => '1', 'PurchaseOrderNumber' => 'PO-001'],
                ['PurchaseOrderID' => '2', 'PurchaseOrderNumber' => 'PO-002'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::purchaseorders()->withFullResponse()->get();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('Id')
        ->and($result)->toHaveKey('Status')
        ->and($result)->toHaveKey('ProviderName')
        ->and($result)->toHaveKey('DateTimeUTC')
        ->and($result)->toHaveKey('PurchaseOrders')
        ->and($result['Id'])->toBe('test-id')
        ->and($result['Status'])->toBe('OK')
        ->and($result['PurchaseOrders'])->toBeArray()
        ->and($result['PurchaseOrders'])->toHaveCount(2);
});

test('find returns purchase order', function () {
    $purchaseOrderId = 'purchase-order-id';

    Http::fake([
        'api.xero.com/api.xro/2.0/PurchaseOrders/'.$purchaseOrderId => Http::response([
            'PurchaseOrders' => [
                ['PurchaseOrderID' => $purchaseOrderId, 'PurchaseOrderNumber' => 'PO-001'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::purchaseorders()->find($purchaseOrderId);

    expect($result)->toBeArray()
        ->and($result['PurchaseOrderID'])->toBe($purchaseOrderId);
});

test('store returns purchase order', function () {
    Http::fake([
        'api.xero.com/api.xro/2.0/PurchaseOrders' => Http::response([
            'PurchaseOrders' => [
                ['PurchaseOrderID' => 'purchase-order-id', 'PurchaseOrderNumber' => 'PO-001'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::purchaseorders()->store([
        'Contact' => ['ContactID' => 'contact-id'],
        'LineItems' => [],
    ]);

    expect($result)->toBeArray()
        ->and($result['PurchaseOrderID'])->toBe('purchase-order-id');
});

test('update returns purchase order', function () {
    $purchaseOrderId = 'purchase-order-id';

    Http::fake([
        'api.xero.com/api.xro/2.0/PurchaseOrders/'.$purchaseOrderId => Http::response([
            'PurchaseOrders' => [
                ['PurchaseOrderID' => $purchaseOrderId, 'PurchaseOrderNumber' => 'PO-001'],
            ],
        ], 200),
    ]);

    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    $result = Xero::purchaseorders()->update($purchaseOrderId, [
        'Status' => 'AUTHORISED',
    ]);

    expect($result)->toBeArray()
        ->and($result['PurchaseOrderID'])->toBe($purchaseOrderId);
});

test('withFullResponse returns self for fluent chaining', function () {
    $purchaseOrders = new PurchaseOrders;

    $result = $purchaseOrders->withFullResponse();

    expect($result)->toBeInstanceOf(PurchaseOrders::class)
        ->and($result)->toBe($purchaseOrders);
});
