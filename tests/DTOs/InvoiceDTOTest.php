<?php

declare(strict_types=1);

use Dcblogdev\Xero\DTOs\InvoiceDTO;
use Dcblogdev\Xero\Enums\InvoiceLineAmountType;
use Dcblogdev\Xero\Enums\InvoiceStatus;
use Dcblogdev\Xero\Enums\InvoiceType;

test('invoice dto can be instantiated with default values', function () {
    $invoiceDTO = new InvoiceDTO();

    expect($invoiceDTO)->toBeInstanceOf(InvoiceDTO::class)
        ->and($invoiceDTO->invoiceID)->toBeNull()
        ->and($invoiceDTO->type)->toBe(InvoiceType::AccRec->value)
        ->and($invoiceDTO->invoiceNumber)->toBeNull()
        ->and($invoiceDTO->reference)->toBeNull()
        ->and($invoiceDTO->date)->toBeNull()
        ->and($invoiceDTO->dueDate)->toBeNull()
        ->and($invoiceDTO->status)->toBe(InvoiceStatus::Draft->value)
        ->and($invoiceDTO->lineAmountTypes)->toBe(InvoiceLineAmountType::Exclusive->value)
        ->and($invoiceDTO->currencyCode)->toBeNull()
        ->and($invoiceDTO->currencyRate)->toBeNull()
        ->and($invoiceDTO->subTotal)->toBeNull()
        ->and($invoiceDTO->totalTax)->toBeNull()
        ->and($invoiceDTO->total)->toBeNull()
        ->and($invoiceDTO->contactID)->toBeNull()
        ->and($invoiceDTO->contact)->toBeNull()
        ->and($invoiceDTO->lineItems)->toBe([])
        ->and($invoiceDTO->payments)->toBe([])
        ->and($invoiceDTO->creditNotes)->toBe([])
        ->and($invoiceDTO->prepayments)->toBe([])
        ->and($invoiceDTO->overpayments)->toBe([])
        ->and($invoiceDTO->hasAttachments)->toBeFalse()
        ->and($invoiceDTO->isDiscounted)->toBeFalse()
        ->and($invoiceDTO->hasErrors)->toBeFalse();
});

test('invoice dto can be instantiated with custom values', function () {
    $invoiceDTO = new InvoiceDTO(
        invoiceID: 'INV-123',
        type: InvoiceType::AccPay->value,
        invoiceNumber: '123',
        reference: 'REF-123',
        date: '2023-01-01',
        dueDate: '2023-01-31',
        status: InvoiceStatus::Submitted->value,
        lineAmountTypes: InvoiceLineAmountType::Inclusive->value,
        currencyCode: 'USD',
        currencyRate: '1.0',
        subTotal: '100.00',
        totalTax: '10.00',
        total: '110.00',
        contactID: 'CONTACT-123',
        contact: [['ContactID' => 'CONTACT-123', 'Name' => 'Test Contact']],
        lineItems: [['Description' => 'Test Item']],
        payments: [['PaymentID' => 'PAYMENT-123']],
        creditNotes: [['CreditNoteID' => 'CREDIT-123']],
        prepayments: [['PrepaymentID' => 'PREPAY-123']],
        overpayments: [['OverpaymentID' => 'OVERPAY-123']],
        hasAttachments: true,
        isDiscounted: true,
        hasErrors: true
    );

    expect($invoiceDTO)->toBeInstanceOf(InvoiceDTO::class)
        ->and($invoiceDTO->invoiceID)->toBe('INV-123')
        ->and($invoiceDTO->type)->toBe(InvoiceType::AccPay->value)
        ->and($invoiceDTO->invoiceNumber)->toBe('123')
        ->and($invoiceDTO->reference)->toBe('REF-123')
        ->and($invoiceDTO->date)->toBe('2023-01-01')
        ->and($invoiceDTO->dueDate)->toBe('2023-01-31')
        ->and($invoiceDTO->status)->toBe(InvoiceStatus::Submitted->value)
        ->and($invoiceDTO->lineAmountTypes)->toBe(InvoiceLineAmountType::Inclusive->value)
        ->and($invoiceDTO->currencyCode)->toBe('USD')
        ->and($invoiceDTO->currencyRate)->toBe('1.0')
        ->and($invoiceDTO->subTotal)->toBe('100.00')
        ->and($invoiceDTO->totalTax)->toBe('10.00')
        ->and($invoiceDTO->total)->toBe('110.00')
        ->and($invoiceDTO->contactID)->toBe('CONTACT-123')
        ->and($invoiceDTO->contact)->toBe([['ContactID' => 'CONTACT-123', 'Name' => 'Test Contact']])
        ->and($invoiceDTO->lineItems)->toBe([['Description' => 'Test Item']])
        ->and($invoiceDTO->payments)->toBe([['PaymentID' => 'PAYMENT-123']])
        ->and($invoiceDTO->creditNotes)->toBe([['CreditNoteID' => 'CREDIT-123']])
        ->and($invoiceDTO->prepayments)->toBe([['PrepaymentID' => 'PREPAY-123']])
        ->and($invoiceDTO->overpayments)->toBe([['OverpaymentID' => 'OVERPAY-123']])
        ->and($invoiceDTO->hasAttachments)->toBeTrue()
        ->and($invoiceDTO->isDiscounted)->toBeTrue()
        ->and($invoiceDTO->hasErrors)->toBeTrue();
});

test('createLineItem static method returns correct array with all values', function () {
    $lineItem = InvoiceDTO::createLineItem(
        description: 'Test Item',
        quantity: 2,
        unitAmount: 10.50,
        accountCode: 123,
        itemCode: 'ITEM-123',
        taxType: 'OUTPUT',
        taxAmount: '2.10',
        lineAmount: '21.00',
        discountRate: '10',
        tracking: [['Name' => 'Region', 'Option' => 'North']]
    );

    expect($lineItem)->toBe([
        'Description' => 'Test Item',
        'Quantity' => 2,
        'UnitAmount' => 10.50,
        'AccountCode' => 123,
        'ItemCode' => 'ITEM-123',
        'TaxType' => 'OUTPUT',
        'TaxAmount' => '2.10',
        'LineAmount' => '21.00',
        'DiscountRate' => '10',
        'Tracking' => [['Name' => 'Region', 'Option' => 'North']],
    ]);
});

test('createLineItem static method filters out null values', function () {
    $lineItem = InvoiceDTO::createLineItem(
        description: 'Test Item',
        quantity: 2,
        unitAmount: 10.50,
        accountCode: null,
        itemCode: null
    );

    expect($lineItem)->toBe([
        'Description' => 'Test Item',
        'Quantity' => 2,
        'UnitAmount' => 10.50,
    ]);
});

test('createLineItem accepts string values for numeric fields', function () {
    $lineItem = InvoiceDTO::createLineItem(
        description: 'Test Item',
        quantity: '2',
        unitAmount: '10.50'
    );

    expect($lineItem)->toBe([
        'Description' => 'Test Item',
        'Quantity' => '2',
        'UnitAmount' => '10.50',
    ]);
});

test('toArray method returns correct array structure', function () {
    $invoiceDTO = new InvoiceDTO(
        type: InvoiceType::AccRec->value,
        invoiceNumber: '123',
        date: '2023-01-01',
        dueDate: '2023-01-31',
        status: InvoiceStatus::Draft->value,
        contactID: 'CONTACT-123',
        lineItems: [
            InvoiceDTO::createLineItem(
                description: 'Test Item',
                quantity: 2,
                unitAmount: 10.50
            ),
        ]
    );

    $array = $invoiceDTO->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('Type', InvoiceType::AccRec->value)
        ->and($array)->toHaveKey('InvoiceNumber', '123')
        ->and($array)->toHaveKey('Date', '2023-01-01')
        ->and($array)->toHaveKey('DueDate', '2023-01-31')
        ->and($array)->toHaveKey('Status', InvoiceStatus::Draft->value)
        ->and($array)->toHaveKey('LineAmountTypes', InvoiceLineAmountType::Exclusive->value)
        ->and($array)->toHaveKey('Contact')
        ->and($array['Contact'])->toBe(['ContactID' => 'CONTACT-123'])
        ->and($array)->toHaveKey('LineItems')
        ->and($array['LineItems'][0])->toHaveKey('Description', 'Test Item')
        ->and($array['LineItems'][0])->toHaveKey('Quantity', 2)
        ->and($array['LineItems'][0])->toHaveKey('UnitAmount', 10.50);
});

test('toArray method uses contact array when contactID is null', function () {
    $invoiceDTO = new InvoiceDTO(
        contact: [
            'ContactID' => 'CONTACT-123',
            'Name' => 'Test Contact',
        ]
    );

    $array = $invoiceDTO->toArray();

    expect($array)->toHaveKey('Contact')
        ->and($array['Contact'])->toBe([
            'ContactID' => 'CONTACT-123',
            'Name' => 'Test Contact',
        ]);
});

test('toArray method filters out null and empty array values', function () {
    $invoiceDTO = new InvoiceDTO(
        invoiceNumber: '123',
        reference: null,
        lineItems: []
    );

    $array = $invoiceDTO->toArray();

    expect($array)->toHaveKey('InvoiceNumber')
        ->and($array)->toHaveKey('Type')
        ->and($array)->toHaveKey('Status')
        ->and($array)->toHaveKey('LineAmountTypes')
        ->and($array)->not->toHaveKey('Reference')
        ->and($array)->not->toHaveKey('LineItems');
});
