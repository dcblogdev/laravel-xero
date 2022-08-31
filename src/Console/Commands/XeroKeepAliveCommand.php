<?php

namespace Dcblogdev\Xero\Console\Commands;

use Dcblogdev\Xero\Facades\Xero;
use Illuminate\Console\Command;

class XeroKeepAliveCommand extends Command
{
    protected $signature   = 'xero:keep-alive';
    protected $description = 'Run this command to refresh token if its due to expire. schedule this to run daily to avoid token expiring when using CLI commands';

    public function handle()
    {
        if (Xero::isConnected()) {
            Xero::getAccessToken($redirectWhenNotConnected = false);
        }
    }
}