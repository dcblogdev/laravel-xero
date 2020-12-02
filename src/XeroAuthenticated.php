<?php

namespace Dcblogdev\Xero;

use Closure;
use Dcblogdev\Xero\Facades\Xero;

class XeroAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Xero::getTokenData() === null) {
            return Xero::connect();
        }

        return $next($request);
    }
}
