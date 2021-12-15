<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Facades\Xero;

class Contacts extends Xero
{
    public function get(int $page = null, string $where = null)
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = Xero::get('contacts?'.$params);

        return $result['body']['Contacts'];
    }

    public function find(string $contactId)
    {
        $result = Xero::get('contacts/'.$contactId);

        return $result['body']['Contacts'][0];
    }

    public function update(string $contactId, array $data) 
    {
        $result = Xero::post('contacts/'.$contactId, $data);

        return $result['body']['Contacts'][0];
    }

    public function store(array $data) 
    {
        $result = Xero::post('contacts', $data);

        return $result['body']['Contacts'][0];
    }
}
