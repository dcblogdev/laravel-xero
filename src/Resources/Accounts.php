<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Xero;
use InvalidArgumentException;

class Accounts extends Xero
{
    protected array $queryString = [];

    public function filter(string $key, string|int $value): self
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

        $result = parent::get('Accounts?'.$queryString);

        return $result['body']['Accounts'];
    }

    public function find(string $accountId): array
    {
        $result = parent::get('Accounts/'.$accountId);

        return $result['body']['Accounts'][0];
    }

    public function store(array $data): array
    {
        $result = parent::put('Accounts', $data);

        return $result['body']['Accounts'][0];
    }

    public function update(string $accountId, array $data): array
    {
        $result = parent::post('Accounts/'.$accountId, $data);

        return $result['body']['Accounts'][0];
    }

    public function archive(string $accountId): array
    {
        $result = parent::post('Accounts/'.$accountId, [
            'AccountID' => $accountId,
            'Status' => 'ARCHIVED',
        ]);

        return $result['body']['Accounts'][0];
    }

    public function delete(string $accountId): void
    {
        parent::delete('Accounts/'.$accountId);
    }

    public function attachments(string $accountId): array
    {
        $result = parent::get('Accounts/'.$accountId.'/Attachments');

        return $result['body']['Attachments'];
    }

    public function attachment(string $accountId, ?string $attachmentId = null, ?string $fileName = null): string
    {
        // Depending on the application, we may want to get it by the FileName instead of the AttachmentId
        $nameOrId = $attachmentId ? $attachmentId : $fileName;

        $result = parent::get('Accounts/'.$accountId.'/Attachments/'.$nameOrId);

        return $result['body'];
    }
}
