<?php

namespace Dcblogdev\Xero\Console\Commands;

use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

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

        $dataToDisplay = [
            'id',
            'tenant_name',
            'tenant_id',
            'updated_at',
        ];

        // Fetch all access tokens
        $tokens = XeroToken::select($dataToDisplay)->get();

        if (config('xero.encrypt')) {
            $tokens->map(function ($token) {
                try {
                    $access_token = Crypt::decryptString($token->access_token);
                } catch (DecryptException $e) {
                    $access_token = $token->access_token;
                }

                // Split them as a refresh token may not exist...
                try {
                    $refresh_token = Crypt::decryptString($token->refresh_token);
                } catch (DecryptException $e) {
                    $refresh_token = $token->refresh_token;
                }

                $token->access_token = $access_token;
                $token->refresh_token = $refresh_token;

                return $token;
            });
        }

        $this->table(
            $dataToDisplay,
            $tokens->toArray()
        );
    }
}
