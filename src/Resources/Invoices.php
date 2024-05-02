<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Xero;

class Invoices extends Xero
{
    public function get(int $page = null, string $where = null)
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = $this->get('invoices?'.$params);

        return $result['body']['Invoices'];
    }

    public function find(string $contactId)
    {
        $result = $this->get('invoices/'.$contactId);

        return $result['body']['Invoices'][0];
    }

    public function onlineUrl(string $invoiceId)
    {
        $result = $this->get('invoices/'.$invoiceId.'/OnlineInvoice');

        return $result['body']['OnlineInvoices'][0]['OnlineInvoiceUrl'];
    }

    public function update(string $invoiceId, array $data)
    {
        $result = $this->post('invoices/'.$invoiceId, $data);

        return $result['body']['Invoices'][0];
    }

    public function store(array $data)
    {
        $result = $this->post('invoices', $data);

        return $result['body']['Invoices'][0];
    }
    
    public function attachments(string $invoiceId)
    {
        $result = Xero::get('invoices/'.$invoiceId.'/Attachments');

        return $result['body']['Attachments'];
    }
    
    public function attachment(string $invoiceId, string $attachmentId = null, string $fileName = null)
    {
        // Depending on the application we may want to get it by the FileName instead fo the AttachmentId
        $nameOrId = $attachmentId ? $attachmentId : $fileName;
        
        $result = Xero::get('invoices/'.$invoiceId.'/Attachments/'.$nameOrId, null, true);

        return $result['body'];
    }    
}
