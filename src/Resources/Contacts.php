<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Xero;
use InvalidArgumentException;

class Contacts extends Xero
{
    protected array $queryString = [];

    public function filter($key, $value): Contacts
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

        $result = parent::get('Contacts?'.$queryString);

        return $result['body']['Contacts'];
    }

    public function find(string $contactId): array
    {
        $result = parent::get('Contacts/'.$contactId);

        return $result['body']['Contacts'][0];
    }

    public function update(string $contactId, array $data): array
    {
        $result = $this->post('Contacts/'.$contactId, $data);

        return $result['body']['Contacts'][0];
    }

    public function store(array $data): array
    {
        $result = $this->post('Contacts', $data);

        return $result['body']['Contacts'][0];
    }
}
