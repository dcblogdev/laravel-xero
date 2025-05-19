<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\DTOs;

use Dcblogdev\Xero\Enums\ContactStatus;

class ContactDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $emailAddress = null,
        public ?string $accountNumber = null,
        public ?string $bankAccountDetails = null,
        public ?string $taxNumber = null,
        public ?string $accountsReceivableTaxType = null,
        public ?string $accountsPayableTaxType = null,
        public ?string $contactStatus = ContactStatus::Active->value,
        public ?bool $isSupplier = false,
        public ?bool $isCustomer = false,
        public ?string $defaultCurrency = null,
        public ?string $website = null,
        public ?string $purchasesDefaultAccountCode = null,
        public ?string $salesDefaultAccountCode = null,
        /** @var array<int, array<string, mixed>> */
        public ?array $addresses = [],
        /** @var array<int, array<string, mixed>> */
        public ?array $phones = [],
        /** @var array<int, array<string, mixed>> */
        public ?array $contactPersons = [],
        public ?bool $hasAttachments = false,
        public ?bool $hasValidationErrors = false,
    ) {}

    /**
     * Create an address array for the contact
     *
     * @return array<string, string|null>
     */
    public static function createAddress(
        string $addressType,
        ?string $addressLine1 = null,
        ?string $addressLine2 = null,
        ?string $addressLine3 = null,
        ?string $addressLine4 = null,
        ?string $city = null,
        ?string $region = null,
        ?string $postalCode = null,
        ?string $country = null,
        ?string $attentionTo = null
    ): array {
        return [
            'AddressType' => $addressType,
            'AddressLine1' => $addressLine1,
            'AddressLine2' => $addressLine2,
            'AddressLine3' => $addressLine3,
            'AddressLine4' => $addressLine4,
            'City' => $city,
            'Region' => $region,
            'PostalCode' => $postalCode,
            'Country' => $country,
            'AttentionTo' => $attentionTo,
        ];
    }

    /**
     * Create a phone array for the contact
     *
     * @return array<string, string|null>
     */
    public static function createPhone(
        string $phoneType,
        ?string $phoneNumber = null,
        ?string $phoneAreaCode = null,
        ?string $phoneCountryCode = null
    ): array {
        return [
            'PhoneType' => $phoneType,
            'PhoneNumber' => $phoneNumber,
            'PhoneAreaCode' => $phoneAreaCode,
            'PhoneCountryCode' => $phoneCountryCode,
        ];
    }

    /**
     * Create a contact person array for the contact
     *
     * @return array<string, string|bool|null>
     */
    public static function createContactPerson(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $emailAddress = null,
        ?bool $includeInEmails = false
    ): array {
        return [
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'EmailAddress' => $emailAddress,
            'IncludeInEmails' => $includeInEmails,
        ];
    }

    /**
     * Convert the DTO to an array for the Xero API
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'Name' => $this->name,
            'FirstName' => $this->firstName,
            'LastName' => $this->lastName,
            'EmailAddress' => $this->emailAddress,
            'AccountNumber' => $this->accountNumber,
            'BankAccountDetails' => $this->bankAccountDetails,
            'TaxNumber' => $this->taxNumber,
            'AccountsReceivableTaxType' => $this->accountsReceivableTaxType,
            'AccountsPayableTaxType' => $this->accountsPayableTaxType,
            'ContactStatus' => $this->contactStatus,
            'IsSupplier' => $this->isSupplier,
            'IsCustomer' => $this->isCustomer,
            'DefaultCurrency' => $this->defaultCurrency,
            'Website' => $this->website,
            'PurchasesDefaultAccountCode' => $this->purchasesDefaultAccountCode,
            'SalesDefaultAccountCode' => $this->salesDefaultAccountCode,
            'Addresses' => $this->addresses,
            'Phones' => $this->phones,
            'ContactPersons' => $this->contactPersons,
            'HasAttachments' => $this->hasAttachments,
            'HasValidationErrors' => $this->hasValidationErrors,
        ], function (mixed $value) {
            return $value !== null;
        });
    }
}
