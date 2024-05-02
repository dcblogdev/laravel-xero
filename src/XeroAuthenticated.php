<?php

namespace Dcblogdev\Xero;

use Closure;
use Dcblogdev\Xero\Facades\Xero;

class XeroAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next): mixed
    {
        if (! Xero::isConnected()) {
            return Xero::connect();
        }

        return $next($request);
    }
}
