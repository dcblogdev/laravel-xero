<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Xero;

class Contacts extends Xero
{
    public function get(int $page = 1, string $where = ''): array
    {
        $params = http_build_query([
            'page' => $page,
            'where' => $where
        ]);

        $result = parent::get('contacts?'.$params);

        return $result['body']['Contacts'];
    }

    public function find(string $contactId): array
    {
        $result = parent::get('contacts/'.$contactId);

        return $result['body']['Contacts'][0];
    }

    public function update(string $contactId, array $data): array
    {
        $result = $this->post('contacts/'.$contactId, $data);

        return $result['body']['Contacts'][0];
    }

    public function store(array $data): array
    {
        $result = $this->post('contacts', $data);

        return $result['body']['Contacts'][0];
    }
}
