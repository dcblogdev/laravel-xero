<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dcblogdev\Xero\Models\XeroToken;

class XeroShowAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xero:show-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command to show all tokens within the database';

    public function handle()
    {
        $this->newLine();
        $this->line('All XERO Tokens in storage');
        $this->newLine();

        $dataToDisplay  = [
            'id',
            'tenant_name',
            'tenant_id',
            'updated_at',
        ];
        // Fetch all access tokens
        $tokens = XeroToken::select($dataToDisplay)->get()->toArray();

        $this->table(
            $dataToDisplay,
            $tokens
        );
    }
}