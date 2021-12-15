<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Facades\Xero;

class Invoices extends Xero
{
    public function get(int $page = null, string $where = null)
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = Xero::get('invoices?'.$params);

        return $result['body']['Invoices'];
    }

    public function find(string $contactId)
    {
        $result = Xero::get('invoices/'.$contactId);

        return $result['body']['Invoices'][0];
    }

    public function onlineUrl(string $invoiceId)
    {
        $result = Xero::get('invoices/'.$invoiceId.'/OnlineInvoice');

        return $result['body']['OnlineInvoices'][0]['OnlineInvoiceUrl'];
    }

    public function update(string $invoiceId, array $data)
    {
        $result = Xero::post('invoices/'.$invoiceId, $data);

        return $result['body']['Invoices'][0];
    }

    public function store(array $data) 
    {
        $result = Xero::post('invoices', $data);

        return $result['body']['Invoices'][0];
    }
}
