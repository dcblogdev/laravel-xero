<?php

use Dcblogdev\Xero\Actions\formatQueryStringsAction;

test('format query strings action', function () {

    $params = [
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ];

    $response = app(formatQueryStringsAction::class)($params);

    expect($response)->toBe('key1=value1&key2=value2&key3=value3');

});

test('throws type error when non-array is passed', function ($value) {
    $this->expectException(TypeError::class);

    app(formatQueryStringsAction::class)($value);
})->with([
    'string',
    123,
    1.23,
    true,
    false,
    null,
    new stdClass(),
]);