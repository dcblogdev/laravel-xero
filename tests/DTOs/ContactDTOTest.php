<?php

declare(strict_types=1);

use Dcblogdev\Xero\DTOs\ContactDTO;
use Dcblogdev\Xero\Enums\ContactStatus;

test('contact dto can be instantiated with default values', function () {
    $contactDTO = new ContactDTO();

    expect($contactDTO)->toBeInstanceOf(ContactDTO::class)
        ->and($contactDTO->name)->toBeNull()
        ->and($contactDTO->firstName)->toBeNull()
        ->and($contactDTO->lastName)->toBeNull()
        ->and($contactDTO->emailAddress)->toBeNull()
        ->and($contactDTO->accountNumber)->toBeNull()
        ->and($contactDTO->bankAccountDetails)->toBeNull()
        ->and($contactDTO->taxNumber)->toBeNull()
        ->and($contactDTO->accountsReceivableTaxType)->toBeNull()
        ->and($contactDTO->accountsPayableTaxType)->toBeNull()
        ->and($contactDTO->contactStatus)->toBe(ContactStatus::Active->value)
        ->and($contactDTO->isSupplier)->toBeFalse()
        ->and($contactDTO->isCustomer)->toBeFalse()
        ->and($contactDTO->defaultCurrency)->toBeNull()
        ->and($contactDTO->website)->toBeNull()
        ->and($contactDTO->purchasesDefaultAccountCode)->toBeNull()
        ->and($contactDTO->salesDefaultAccountCode)->toBeNull()
        ->and($contactDTO->addresses)->toBe([])
        ->and($contactDTO->phones)->toBe([])
        ->and($contactDTO->contactPersons)->toBe([])
        ->and($contactDTO->hasAttachments)->toBeFalse()
        ->and($contactDTO->hasValidationErrors)->toBeFalse();
});

test('contact dto can be instantiated with custom values', function () {
    $contactDTO = new ContactDTO(
        name: 'Test Company',
        firstName: 'John',
        lastName: 'Doe',
        emailAddress: 'john.doe@example.com',
        accountNumber: 'ACC123',
        bankAccountDetails: '123456789',
        taxNumber: 'TAX123',
        accountsReceivableTaxType: 'OUTPUT',
        accountsPayableTaxType: 'INPUT',
        contactStatus: ContactStatus::Archived->value,
        isSupplier: true,
        isCustomer: true,
        defaultCurrency: 'USD',
        website: 'https://example.com',
        purchasesDefaultAccountCode: 'P123',
        salesDefaultAccountCode: 'S123',
        addresses: [['AddressType' => 'POBOX']],
        phones: [['PhoneType' => 'MOBILE']],
        contactPersons: [['FirstName' => 'Jane']],
        hasAttachments: true,
        hasValidationErrors: true
    );

    expect($contactDTO)->toBeInstanceOf(ContactDTO::class)
        ->and($contactDTO->name)->toBe('Test Company')
        ->and($contactDTO->firstName)->toBe('John')
        ->and($contactDTO->lastName)->toBe('Doe')
        ->and($contactDTO->emailAddress)->toBe('john.doe@example.com')
        ->and($contactDTO->accountNumber)->toBe('ACC123')
        ->and($contactDTO->bankAccountDetails)->toBe('123456789')
        ->and($contactDTO->taxNumber)->toBe('TAX123')
        ->and($contactDTO->accountsReceivableTaxType)->toBe('OUTPUT')
        ->and($contactDTO->accountsPayableTaxType)->toBe('INPUT')
        ->and($contactDTO->contactStatus)->toBe(ContactStatus::Archived->value)
        ->and($contactDTO->isSupplier)->toBeTrue()
        ->and($contactDTO->isCustomer)->toBeTrue()
        ->and($contactDTO->defaultCurrency)->toBe('USD')
        ->and($contactDTO->website)->toBe('https://example.com')
        ->and($contactDTO->purchasesDefaultAccountCode)->toBe('P123')
        ->and($contactDTO->salesDefaultAccountCode)->toBe('S123')
        ->and($contactDTO->addresses)->toBe([['AddressType' => 'POBOX']])
        ->and($contactDTO->phones)->toBe([['PhoneType' => 'MOBILE']])
        ->and($contactDTO->contactPersons)->toBe([['FirstName' => 'Jane']])
        ->and($contactDTO->hasAttachments)->toBeTrue()
        ->and($contactDTO->hasValidationErrors)->toBeTrue();
});

test('createAddress static method returns correct array', function () {
    $address = ContactDTO::createAddress(
        addressType: 'POBOX',
        addressLine1: '123 Main St',
        addressLine2: 'Suite 100',
        addressLine3: 'Building A',
        addressLine4: 'Floor 2',
        city: 'Anytown',
        region: 'State',
        postalCode: '12345',
        country: 'USA',
        attentionTo: 'John Doe'
    );

    expect($address)->toBe([
        'AddressType' => 'POBOX',
        'AddressLine1' => '123 Main St',
        'AddressLine2' => 'Suite 100',
        'AddressLine3' => 'Building A',
        'AddressLine4' => 'Floor 2',
        'City' => 'Anytown',
        'Region' => 'State',
        'PostalCode' => '12345',
        'Country' => 'USA',
        'AttentionTo' => 'John Doe',
    ]);
});

test('createPhone static method returns correct array', function () {
    $phone = ContactDTO::createPhone(
        phoneType: 'MOBILE',
        phoneNumber: '555-1234',
        phoneAreaCode: '123',
        phoneCountryCode: '1'
    );

    expect($phone)->toBe([
        'PhoneType' => 'MOBILE',
        'PhoneNumber' => '555-1234',
        'PhoneAreaCode' => '123',
        'PhoneCountryCode' => '1',
    ]);
});

test('createContactPerson static method returns correct array', function () {
    $contactPerson = ContactDTO::createContactPerson(
        firstName: 'Jane',
        lastName: 'Smith',
        emailAddress: 'jane.smith@example.com',
        includeInEmails: true
    );

    expect($contactPerson)->toBe([
        'FirstName' => 'Jane',
        'LastName' => 'Smith',
        'EmailAddress' => 'jane.smith@example.com',
        'IncludeInEmails' => true,
    ]);
});

test('toArray method returns correct array structure', function () {
    $contactDTO = new ContactDTO(
        name: 'Test Company',
        firstName: 'John',
        lastName: 'Doe',
        emailAddress: 'john.doe@example.com',
        isSupplier: true,
        addresses: [
            ContactDTO::createAddress('POBOX', '123 Main St'),
        ],
        phones: [
            ContactDTO::createPhone('MOBILE', '555-1234'),
        ],
        contactPersons: [
            ContactDTO::createContactPerson('Jane', 'Smith'),
        ]
    );

    $array = $contactDTO->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('Name', 'Test Company')
        ->and($array)->toHaveKey('FirstName', 'John')
        ->and($array)->toHaveKey('LastName', 'Doe')
        ->and($array)->toHaveKey('EmailAddress', 'john.doe@example.com')
        ->and($array)->toHaveKey('ContactStatus', ContactStatus::Active->value)
        ->and($array)->toHaveKey('IsSupplier', true)
        ->and($array)->toHaveKey('IsCustomer', false)
        ->and($array)->toHaveKey('Addresses')
        ->and($array['Addresses'][0])->toHaveKey('AddressType', 'POBOX')
        ->and($array['Addresses'][0])->toHaveKey('AddressLine1', '123 Main St')
        ->and($array)->toHaveKey('Phones')
        ->and($array['Phones'][0])->toHaveKey('PhoneType', 'MOBILE')
        ->and($array['Phones'][0])->toHaveKey('PhoneNumber', '555-1234')
        ->and($array)->toHaveKey('ContactPersons')
        ->and($array['ContactPersons'][0])->toHaveKey('FirstName', 'Jane')
        ->and($array['ContactPersons'][0])->toHaveKey('LastName', 'Smith');
});

test('toArray method filters out null values', function () {
    $contactDTO = new ContactDTO(
        name: 'Test Company',
        emailAddress: null,
        website: null
    );

    $array = $contactDTO->toArray();

    expect($array)->toHaveKey('Name')
        ->and($array)->not->toHaveKey('EmailAddress')
        ->and($array)->not->toHaveKey('Website');
});
