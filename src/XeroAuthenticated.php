<?php

declare(strict_types=1);

namespace Dcblogdev\Xero;

use Closure;
use Dcblogdev\Xero\Facades\Xero;
use Exception;
use Illuminate\Http\Request;

class XeroAuthenticated
{
    /**
     * Handle an incoming request.
     *
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! Xero::isConnected()) {
            return Xero::connect();
        }

        return $next($request);
    }
}
