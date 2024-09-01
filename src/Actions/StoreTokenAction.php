<?php

namespace Dcblogdev\Xero\Actions;

use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Crypt;

class StoreTokenAction
{
    public function __invoke(array $token, array $tenantData = [], string $tenantId = null): XeroToken
    {
        $data = [
            'id_token' => $token['id_token'],
            'access_token' => config('xero.encrypt') ? Crypt::encryptString($token['access_token']) : $token['access_token'],
            'expires_in' => $token['expires_in'],
            'token_type' => $token['token_type'],
            'refresh_token' => config('xero.encrypt') ? Crypt::encryptString($token['refresh_token']) : $token['refresh_token'],
            'scopes' => $token['scope'],
        ];

        if ($tenantId) {
            $where = ['tenant_id' => $tenantId];
        } elseif ($tenantData !== []) {
            $data = array_merge($data, $tenantData);
            $where = ['tenant_id' => $data['tenant_id']];
        } else {
            $where = ['id' => 1];
        }

        return XeroToken::updateOrCreate($where, $data);
    }
}