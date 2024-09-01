<?php

namespace Dcblogdev\Xero\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array get (string $endpoint, array $params = [])
 * @method static array put (string $endpoint, array $params = [])
 * @method static array post (string $endpoint, array $params = [])
 * @method static array patch (string $endpoint, array $params = [])
 * @method static array delete (string $endpoint, array $params = [])
 *
 * @mixin \Dcblogdev\Xero\Xero
 */
class Xero extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'xero';
    }
}
