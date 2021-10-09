<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Facades\Xero;

class Payments extends XeroModified
{
    public function payments()
    {
        return new Payments();
    }

    public function get(int $page = 1, string $where = null)
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = Xero::get('payments?'.$params);

        return $result['body']['Payments'];
    }

    public function find(string $paymentId)
    {
        $result = Xero::get('payments/'.$paymentId);

        return $result['body']['Payments'][0];
    }

    public function update(string $paymentId, array $data)
    {
        $result = Xero::post('payments/'.$paymentId, $data);

        return $result['body']['Payments'][0];
    }

    public function store(array $data) 
    {
        $result = Xero::post('payments', $data);

        return $result['body']['Payments'][0];
    }
}