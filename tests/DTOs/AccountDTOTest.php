<?php

declare(strict_types=1);

use Dcblogdev\Xero\DTOs\AccountDTO;
use Dcblogdev\Xero\Enums\AccountClass;
use Dcblogdev\Xero\Enums\AccountStatus;
use Dcblogdev\Xero\Enums\AccountType;

test('account dto can be instantiated with default values', function () {
    $accountDTO = new AccountDTO();

    expect($accountDTO)->toBeInstanceOf(AccountDTO::class)
        ->and($accountDTO->accountID)->toBeNull()
        ->and($accountDTO->code)->toBeNull()
        ->and($accountDTO->name)->toBeNull()
        ->and($accountDTO->type)->toBeNull()
        ->and($accountDTO->status)->toBe(AccountStatus::Active->value)
        ->and($accountDTO->description)->toBeNull()
        ->and($accountDTO->bankAccountNumber)->toBeNull()
        ->and($accountDTO->bankAccountType)->toBeNull()
        ->and($accountDTO->currencyCode)->toBeNull()
        ->and($accountDTO->taxType)->toBeNull()
        ->and($accountDTO->enablePaymentsToAccount)->toBeFalse()
        ->and($accountDTO->showInExpenseClaims)->toBeFalse()
        ->and($accountDTO->addToWatchlist)->toBeFalse()
        ->and($accountDTO->class)->toBeNull()
        ->and($accountDTO->systemAccount)->toBeNull()
        ->and($accountDTO->reportingCode)->toBeNull()
        ->and($accountDTO->reportingCodeUpdatedUTC)->toBeNull()
        ->and($accountDTO->reportingCodeName)->toBeNull()
        ->and($accountDTO->hasAttachments)->toBeFalse()
        ->and($accountDTO->updatedDateUTC)->toBeNull();
});

test('account dto can be instantiated with custom values', function () {
    $accountDTO = new AccountDTO(
        accountID: 'ACC-123',
        code: '200',
        name: 'Sales Account',
        type: AccountType::Revenue->value,
        status: AccountStatus::Active->value,
        description: 'Income from sales',
        bankAccountNumber: '1234567890',
        bankAccountType: 'BANK',
        currencyCode: 'USD',
        taxType: 'OUTPUT2',
        enablePaymentsToAccount: true,
        showInExpenseClaims: true,
        addToWatchlist: true,
        class: AccountClass::Revenue->value,
        systemAccount: 'SALES',
        reportingCode: 'REP001',
        reportingCodeUpdatedUTC: '2023-01-01T00:00:00',
        reportingCodeName: 'Sales Reporting',
        hasAttachments: true,
        updatedDateUTC: '2023-01-01T00:00:00'
    );

    expect($accountDTO)->toBeInstanceOf(AccountDTO::class)
        ->and($accountDTO->accountID)->toBe('ACC-123')
        ->and($accountDTO->code)->toBe('200')
        ->and($accountDTO->name)->toBe('Sales Account')
        ->and($accountDTO->type)->toBe(AccountType::Revenue->value)
        ->and($accountDTO->status)->toBe(AccountStatus::Active->value)
        ->and($accountDTO->description)->toBe('Income from sales')
        ->and($accountDTO->bankAccountNumber)->toBe('1234567890')
        ->and($accountDTO->bankAccountType)->toBe('BANK')
        ->and($accountDTO->currencyCode)->toBe('USD')
        ->and($accountDTO->taxType)->toBe('OUTPUT2')
        ->and($accountDTO->enablePaymentsToAccount)->toBeTrue()
        ->and($accountDTO->showInExpenseClaims)->toBeTrue()
        ->and($accountDTO->addToWatchlist)->toBeTrue()
        ->and($accountDTO->class)->toBe(AccountClass::Revenue->value)
        ->and($accountDTO->systemAccount)->toBe('SALES')
        ->and($accountDTO->reportingCode)->toBe('REP001')
        ->and($accountDTO->reportingCodeUpdatedUTC)->toBe('2023-01-01T00:00:00')
        ->and($accountDTO->reportingCodeName)->toBe('Sales Reporting')
        ->and($accountDTO->hasAttachments)->toBeTrue()
        ->and($accountDTO->updatedDateUTC)->toBe('2023-01-01T00:00:00');
});

test('toArray method returns correct array structure', function () {
    $accountDTO = new AccountDTO(
        code: '200',
        name: 'Sales Account',
        type: AccountType::Revenue->value,
        status: AccountStatus::Active->value,
        description: 'Income from sales',
        enablePaymentsToAccount: true,
        hasAttachments: false
    );

    $array = $accountDTO->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('Code', '200')
        ->and($array)->toHaveKey('Name', 'Sales Account')
        ->and($array)->toHaveKey('Type', AccountType::Revenue->value)
        ->and($array)->toHaveKey('Status', AccountStatus::Active->value)
        ->and($array)->toHaveKey('Description', 'Income from sales')
        ->and($array)->toHaveKey('EnablePaymentsToAccount', true)
        ->and($array)->toHaveKey('HasAttachments', false);
});

test('toArray method filters out null values', function () {
    $accountDTO = new AccountDTO(
        code: '200',
        name: 'Sales Account',
        type: AccountType::Revenue->value,
        description: null,
        bankAccountNumber: null,
        currencyCode: null
    );

    $array = $accountDTO->toArray();

    expect($array)->toHaveKey('Code')
        ->and($array)->toHaveKey('Name')
        ->and($array)->toHaveKey('Type')
        ->and($array)->toHaveKey('Status')
        ->and($array)->not->toHaveKey('Description')
        ->and($array)->not->toHaveKey('BankAccountNumber')
        ->and($array)->not->toHaveKey('CurrencyCode');
});
