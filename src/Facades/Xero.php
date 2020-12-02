<?php

namespace Dcblogdev\Xero\Facades;

use Illuminate\Support\Facades\Facade;

class Xero extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'xero';
    }
}
