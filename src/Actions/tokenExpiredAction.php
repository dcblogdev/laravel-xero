<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Actions;

use Dcblogdev\Xero\Models\XeroToken;
use Exception;
use Illuminate\Http\RedirectResponse;

class tokenExpiredAction
{
    /**
     * @throws Exception
     */
    public function __invoke(array $result, XeroToken $token): ?RedirectResponse
    {
        if (isset($result['error']) && $result['error'] === 'invalid_grant') {
            $token->delete();

            if (app()->runningInConsole()) {
                throw new Exception('Xero token has expired, please re-authenticate.');
            }

            return redirect()->away(config('xero.redirectUri'));

        }

        return null;
    }
}
