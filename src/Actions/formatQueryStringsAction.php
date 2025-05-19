<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Actions;

class formatQueryStringsAction
{
    public function __invoke(array $params): string
    {
        $queryString = '';

        foreach ($params as $key => $value) {
            $queryString .= "$key=$value&";
        }

        return mb_rtrim($queryString, '&');
    }
}
