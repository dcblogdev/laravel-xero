<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Xero;

class Webhooks extends Xero
{
    protected string $payload;

    public function validate(): bool
    {
        $this->payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_XERO_SIGNATURE'];

        return hash_equals($this->getSignature(), $signature);
    }

    public function getSignature(): string
    {
        return base64_encode(hash_hmac('sha256', $this->payload, config('xero.webhookKey'), true));
    }

    public function getEvents(): array
    {
        $this->validate();

        $payload = json_decode($this->payload);

        return $payload->events;
    }
}
