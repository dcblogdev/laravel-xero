<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Actions;

class formatQueryStringsAction
{
    public function __invoke(array $params): string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}
