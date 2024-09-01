<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Xero;
use InvalidArgumentException;

class Invoices extends Xero
{
    protected array $queryString = [];

    public function filter($key, $value): Invoices
    {
        if (! FilterOptions::isValid($key)) {
            throw new InvalidArgumentException("Filter option '$key' is not valid.");
        }

        $this->queryString[$key] = $value;

        return $this;
    }

    public function get(): array
    {
        $queryString = $this->formatQueryStrings($this->queryString);

        $result = parent::get('Invoices?'.$queryString);

        return $result['body']['Invoices'];
    }

    public function find(string $invoiceId): array
    {
        $result = parent::get('Invoices/'.$invoiceId);

        return $result['body']['Invoices'][0];
    }

    public function onlineUrl(string $invoiceId): string
    {
        $result = parent::get('Invoices/'.$invoiceId.'/OnlineInvoice');

        return $result['body']['OnlineInvoices'][0]['OnlineInvoiceUrl'];
    }

    public function update(string $invoiceId, array $data): array
    {
        $result = parent::post('Invoices/'.$invoiceId, $data);

        return $result['body']['Invoices'][0];
    }

    public function store(array $data): array
    {
        $result = parent::post('Invoices', $data);

        return $result['body']['Invoices'][0];
    }

    public function attachments(string $invoiceId): array
    {
        $result = parent::get('Invoices/'.$invoiceId.'/Attachments');

        return $result['body']['Attachments'];
    }

    public function attachment(string $invoiceId, ?string $attachmentId = null, ?string $fileName = null): string
    {
        // Depending on the application we may want to get it by the FileName instead fo the AttachmentId
        $nameOrId = $attachmentId ? $attachmentId : $fileName;

        $result = parent::get('Invoices/'.$invoiceId.'/Attachments/'.$nameOrId);

        return $result['body'];
    }
}
