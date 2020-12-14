<?php

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Facades\Xero;

class Webhooks extends Xero
{
    protected $payload;

    public function validate()
    {
        $this->payload = file_get_contents("php://input");
        $signature = $_SERVER['HTTP_X_XERO_SIGNATURE'];

        return hash_equals($this->getSignature(), $signature);
    }

    public function getSignature()
    {
        return base64_encode(hash_hmac('sha256', $this->payload, config('xero.webhookKey'), true));
    }

    public function getEvents()
    {
        $this->validate();

        $payload = json_decode($this->payload);

        return $payload->events;
    }

}