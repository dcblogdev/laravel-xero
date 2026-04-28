<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Http;

test('resource store methods pass custom headers', function () {
    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts' => Http::response([
            'Contacts' => [['ContactID' => 'contact-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/CreditNotes' => Http::response([
            'CreditNotes' => [['CreditNoteID' => 'credit-note-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/Invoices' => Http::response([
            'Invoices' => [['InvoiceID' => 'invoice-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/PurchaseOrders' => Http::response([
            'PurchaseOrders' => [['PurchaseOrderID' => 'purchase-order-id']],
        ], 200),
    ]);

    $headers = ['Idempotency-Key' => 'store-idempotency-key'];

    Xero::contacts()->store(['Name' => 'Test Contact'], $headers);
    Xero::creditnotes()->store(['Contact' => ['ContactID' => 'contact-id']], $headers);
    Xero::invoices()->store(['Contact' => ['ContactID' => 'contact-id']], $headers);
    Xero::purchaseorders()->store(['Contact' => ['ContactID' => 'contact-id']], $headers);

    foreach (['Contacts', 'CreditNotes', 'Invoices', 'PurchaseOrders'] as $resource) {
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === "https://api.xero.com/api.xro/2.0/{$resource}"
            && $request->hasHeader('Idempotency-Key', 'store-idempotency-key')
            && $request->hasHeader('Xero-tenant-id', 'test-tenant'));
    }
});

test('resource update methods pass custom headers', function () {
    XeroToken::create([
        'id' => 0,
        'access_token' => '1234',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Contacts/contact-id' => Http::response([
            'Contacts' => [['ContactID' => 'contact-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/CreditNotes/credit-note-id' => Http::response([
            'CreditNotes' => [['CreditNoteID' => 'credit-note-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/Invoices/invoice-id' => Http::response([
            'Invoices' => [['InvoiceID' => 'invoice-id']],
        ], 200),
        'api.xero.com/api.xro/2.0/PurchaseOrders/purchase-order-id' => Http::response([
            'PurchaseOrders' => [['PurchaseOrderID' => 'purchase-order-id']],
        ], 200),
    ]);

    $headers = ['Idempotency-Key' => 'update-idempotency-key'];

    Xero::contacts()->update('contact-id', ['Name' => 'Updated Contact'], $headers);
    Xero::creditnotes()->update('credit-note-id', ['Reference' => 'Updated Credit Note'], $headers);
    Xero::invoices()->update('invoice-id', ['Reference' => 'Updated Invoice'], $headers);
    Xero::purchaseorders()->update('purchase-order-id', ['Reference' => 'Updated Purchase Order'], $headers);

    foreach ([
        'Contacts/contact-id',
        'CreditNotes/credit-note-id',
        'Invoices/invoice-id',
        'PurchaseOrders/purchase-order-id',
    ] as $resource) {
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === "https://api.xero.com/api.xro/2.0/{$resource}"
            && $request->hasHeader('Idempotency-Key', 'update-idempotency-key')
            && $request->hasHeader('Xero-tenant-id', 'test-tenant'));
    }
});
