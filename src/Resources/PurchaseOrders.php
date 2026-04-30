<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Xero;
use InvalidArgumentException;

class PurchaseOrders extends Xero
{
    protected array $queryString = [];

    public function filter(string $key, string|int $value): static
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

        $result = parent::get('PurchaseOrders?'.$queryString);

        if ($this->returnFullResponse) {
            return $result['body'];
        }

        return $result['body']['PurchaseOrders'];
    }

    public function find(string $purchaseOrderId): array
    {
        $result = parent::get('PurchaseOrders/'.$purchaseOrderId);

        return $result['body']['PurchaseOrders'][0];
    }

    public function update(string $purchaseOrderId, array $data): array
    {
        $result = $this->post('PurchaseOrders/'.$purchaseOrderId, $data);

        return $result['body']['PurchaseOrders'][0];
    }

    public function store(array $data): array
    {
        $result = $this->post('PurchaseOrders', $data);

        return $result['body']['PurchaseOrders'][0];
    }
}
