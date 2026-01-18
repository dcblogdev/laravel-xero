<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\DTOs;

use Dcblogdev\Xero\Enums\AccountStatus;

class AccountDTO
{
    public function __construct(
        public ?string $accountID = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $type = null,
        public ?string $status = AccountStatus::Active->value,
        public ?string $description = null,
        public ?string $bankAccountNumber = null,
        public ?string $bankAccountType = null,
        public ?string $currencyCode = null,
        public ?string $taxType = null,
        public ?bool $enablePaymentsToAccount = false,
        public ?bool $showInExpenseClaims = false,
        public ?bool $addToWatchlist = false,
        public ?string $class = null,
        public ?string $systemAccount = null,
        public ?string $reportingCode = null,
        public ?string $reportingCodeUpdatedUTC = null,
        public ?string $reportingCodeName = null,
        public ?bool $hasAttachments = false,
        public ?string $updatedDateUTC = null,
    ) {}

    /**
     * Convert the DTO to an array for the Xero API
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'AccountID' => $this->accountID,
            'Code' => $this->code,
            'Name' => $this->name,
            'Type' => $this->type,
            'Status' => $this->status,
            'Description' => $this->description,
            'BankAccountNumber' => $this->bankAccountNumber,
            'BankAccountType' => $this->bankAccountType,
            'CurrencyCode' => $this->currencyCode,
            'TaxType' => $this->taxType,
            'EnablePaymentsToAccount' => $this->enablePaymentsToAccount,
            'ShowInExpenseClaims' => $this->showInExpenseClaims,
            'AddToWatchlist' => $this->addToWatchlist,
            'Class' => $this->class,
            'SystemAccount' => $this->systemAccount,
            'ReportingCode' => $this->reportingCode,
            'ReportingCodeUpdatedUTC' => $this->reportingCodeUpdatedUTC,
            'ReportingCodeName' => $this->reportingCodeName,
            'HasAttachments' => $this->hasAttachments,
            'UpdatedDateUTC' => $this->updatedDateUTC,
        ], function (mixed $value) {
            return $value !== null;
        });
    }
}
