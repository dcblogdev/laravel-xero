<?php

namespace Dcblogdev\Xero\Console\Commands;

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Console\Command;

class XeroKeepAliveCommand extends Command
{
    protected $signature = 'xero:keep-alive';

    protected $description = 'Run this command to refresh token if its due to expire. schedule this to run daily to avoid token expiring when using CLI commands';

    public function handle(): void
    {
        $this->newLine();
        // Fetch all tenants for when multiple tenants are in use.
        $tenants = XeroToken::all();

        foreach ($tenants as $tenant) {

            // Set the tenant ID
            Xero::setTenantId($tenant->tenant_id);

            if (Xero::isConnected()) {
                Xero::getAccessToken($redirectWhenNotConnected = false);
                $this->info('Refreshing Token for Tenant: '.$tenant->tenant_name.' - Successful');
            } else {
                $this->info('Refreshing Token for Tenant: '.$tenant->tenant_name.' - Not Connected');
            }
        }
    }
}
