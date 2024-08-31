<?php

namespace Dcblogdev\Xero\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class XeroTokenExpiredException extends Exception
{
    public function render(): RedirectResponse
    {
        return redirect()->away(config('xero.redirectUri'))->with('error', $this->getMessage());
    }
}