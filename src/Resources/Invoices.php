<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Xero;

class Invoices extends Xero
{
    public function get(int $page = 1, string $where = ''): array
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = parent::get('invoices?'.$params);

        return $result['body']['Invoices'];
    }

    public function find(string $contactId): array
    {
        $result = parent::get('invoices/'.$contactId);

        return $result['body']['Invoices'][0];
    }

    public function onlineUrl(string $invoiceId): string
    {
        $result = parent::get('invoices/'.$invoiceId.'/OnlineInvoice');

        return $result['body']['OnlineInvoices'][0]['OnlineInvoiceUrl'];
    }

    public function update(string $invoiceId, array $data): array
    {
        $result = parent::post('invoices/'.$invoiceId, $data);

        return $result['body']['Invoices'][0];
    }

    public function store(array $data): array
    {
        $result = parent::post('invoices', $data);

        return $result['body']['Invoices'][0];
    }
    
    public function attachments(string $invoiceId): array
    {
        $result = parent::get('invoices/'.$invoiceId.'/Attachments');

        return $result['body']['Attachments'];
    }
    
    public function attachment(string $invoiceId, string $attachmentId = null, string $fileName = null): string
    {
        // Depending on the application we may want to get it by the FileName instead fo the AttachmentId
        $nameOrId = $attachmentId ? $attachmentId : $fileName;
        
        $result = parent::get('invoices/'.$invoiceId.'/Attachments/'.$nameOrId);

        return $result['body'];
    }
}
