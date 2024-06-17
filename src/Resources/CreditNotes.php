<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Xero;
use InvalidArgumentException;

class CreditNotes extends Xero
{
    protected array $queryString = [];

    public function filter($key, $value): static
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

        $result = parent::get('CreditNotes?'.$queryString);

        return $result['body']['CreditNotes'];
    }

    public function find(string $contactId): array
    {
        $result = parent::get('CreditNotes/'.$contactId);

        return $result['body']['CreditNotes'][0];
    }

    public function update(string $contactId, array $data): array
    {
        $result = $this->post('CreditNotes/'.$contactId, $data);

        return $result['body']['CreditNotes'][0];
    }

    public function store(array $data): array
    {
        $result = $this->post('CreditNotes', $data);

        return $result['body']['CreditNotes'][0];
    }
}
