<?php

declare(strict_types=1);

use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Resources\Invoices;
use Illuminate\Support\Facades\Http;

test('invalid filter option throws exception', function () {
    Xero::invoices()
        ->filter('bogus', 1)
        ->get();
})->throws(InvalidArgumentException::class, "Filter option 'bogus' is not valid.");

test('filter returns object', function () {

    $filter = (new Invoices)->filter('ids', '1234');

    expect($filter)->toBeObject();
});

// Email method tests
test('email returns structured response on successful send', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId.'/Email' => Http::response('', 204),
    ]);

    $result = Xero::invoices()->email($invoiceId);

    expect($result)->toBe([
        'status' => 204,
        'success' => true,
        'message' => 'Invoice email sent successfully',
        'body' => [],
    ]);
});

test('email returns structured error response on validation error', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    $errorResponse = [
        'Type' => 'ValidationException',
        'Message' => 'Invoice cannot be emailed',
        'Detail' => 'Invoice status is invalid for emailing',
    ];

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId.'/Email' => Http::response($errorResponse, 400),
    ]);

    $result = Xero::invoices()->email($invoiceId);

    expect($result)->toHaveKeys(['status', 'success', 'errors', 'message', 'body'])
        ->and($result['status'])->toBe(400)
        ->and($result['success'])->toBeFalse()
        ->and($result['message'])->toBe('Invoice cannot be emailed')
        ->and($result['errors'])->toBe($errorResponse)
        ->and($result['body'])->toBe($errorResponse);
});

test('email returns structured error response on rate limit error', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    $errorResponse = [
        'Type' => 'RateLimitException',
        'Message' => 'Rate limit exceeded',
        'Detail' => 'Daily email limit reached',
    ];

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId.'/Email' => Http::response($errorResponse, 400),
    ]);

    $result = Xero::invoices()->email($invoiceId);

    expect($result)->toHaveKeys(['status', 'success', 'errors', 'message', 'body'])
        ->and($result['status'])->toBe(400)
        ->and($result['success'])->toBeFalse()
        ->and($result['message'])->toBe('Rate limit exceeded')
        ->and($result['errors'])->toBe($errorResponse)
        ->and($result['body'])->toBe($errorResponse);
});

test('email throws exception on non-400 errors', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId.'/Email' => Http::response([
            'Type' => 'ServerError',
            'Message' => 'Internal server error',
        ], 500),
    ]);

    Xero::invoices()->email($invoiceId);
})->throws(Exception::class);

// Get email recipients tests
test('getEmailRecipients returns primary contact email', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';
    $contactId = '9fe61eb6-e99e-436b-91e7-872b3418681e';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Contact' => [
                        'ContactID' => $contactId,
                    ],
                ],
            ],
        ]),
        'api.xero.com/api.xro/2.0/Contacts/'.$contactId => Http::response([
            'Contacts' => [
                [
                    'ContactID' => $contactId,
                    'EmailAddress' => 'jon.baird@tpfire.co.uk',
                    'ContactPersons' => [],
                ],
            ],
        ]),
    ]);

    $recipients = Xero::invoices()->getEmailRecipients($invoiceId);

    expect($recipients)->toBe(['jon.baird@tpfire.co.uk']);
});

test('getEmailRecipients includes contact persons with IncludeInEmails true', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';
    $contactId = '9fe61eb6-e99e-436b-91e7-872b3418681e';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Contact' => [
                        'ContactID' => $contactId,
                    ],
                ],
            ],
        ]),
        'api.xero.com/api.xro/2.0/Contacts/'.$contactId => Http::response([
            'Contacts' => [
                [
                    'ContactID' => $contactId,
                    'EmailAddress' => 'jon.baird@tpfire.co.uk',
                    'ContactPersons' => [
                        [
                            'FirstName' => 'Jamie',
                            'LastName' => 'Groom',
                            'EmailAddress' => 'Jamie.Groom@tpfire.co.uk',
                            'IncludeInEmails' => true,
                        ],
                        [
                            'FirstName' => 'John',
                            'LastName' => 'Doe',
                            'EmailAddress' => 'john.doe@tpfire.co.uk',
                            'IncludeInEmails' => false,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $recipients = Xero::invoices()->getEmailRecipients($invoiceId);

    expect($recipients)->toHaveCount(2)
        ->and($recipients)->toContain('jon.baird@tpfire.co.uk')
        ->and($recipients)->toContain('Jamie.Groom@tpfire.co.uk')
        ->and($recipients)->not->toContain('john.doe@tpfire.co.uk');
});

test('getEmailRecipients returns empty array when no contact ID', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Contact' => [],
                ],
            ],
        ]),
    ]);

    $recipients = Xero::invoices()->getEmailRecipients($invoiceId);

    expect($recipients)->toBe([]);
});

test('getEmailRecipients returns empty array when no email address', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';
    $contactId = '9fe61eb6-e99e-436b-91e7-872b3418681e';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Contact' => [
                        'ContactID' => $contactId,
                    ],
                ],
            ],
        ]),
        'api.xero.com/api.xro/2.0/Contacts/'.$contactId => Http::response([
            'Contacts' => [
                [
                    'ContactID' => $contactId,
                    'ContactPersons' => [],
                ],
            ],
        ]),
    ]);

    $recipients = Xero::invoices()->getEmailRecipients($invoiceId);

    expect($recipients)->toBe([]);
});

// Can email validation tests
test('canEmail returns true for ACCREC invoice with SUBMITTED status', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Type' => 'ACCREC',
                    'Status' => 'SUBMITTED',
                ],
            ],
        ]),
    ]);

    $result = Xero::invoices()->canEmail($invoiceId);

    expect($result)->toBeTrue();
});

test('canEmail returns true for ACCREC invoice with AUTHORISED status', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Type' => 'ACCREC',
                    'Status' => 'AUTHORISED',
                ],
            ],
        ]),
    ]);

    $result = Xero::invoices()->canEmail($invoiceId);

    expect($result)->toBeTrue();
});

test('canEmail returns true for ACCREC invoice with PAID status', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Type' => 'ACCREC',
                    'Status' => 'PAID',
                ],
            ],
        ]),
    ]);

    $result = Xero::invoices()->canEmail($invoiceId);

    expect($result)->toBeTrue();
});

test('canEmail returns false for ACCREC invoice with DRAFT status', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Type' => 'ACCREC',
                    'Status' => 'DRAFT',
                ],
            ],
        ]),
    ]);

    $result = Xero::invoices()->canEmail($invoiceId);

    expect($result)->toBeFalse();
});

test('canEmail returns false for ACCPAY invoice type', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Invoices' => [
                [
                    'InvoiceID' => $invoiceId,
                    'Type' => 'ACCPAY',
                    'Status' => 'AUTHORISED',
                ],
            ],
        ]),
    ]);

    $result = Xero::invoices()->canEmail($invoiceId);

    expect($result)->toBeFalse();
});

test('canEmail throws exception when invoice not found', function () {
    $invoiceId = 'aa682059-c8ec-44b9-bc7f-344c94e1ffae';

    XeroToken::create([
        'id' => 0,
        'access_token' => 'test-token',
        'expires_in' => now()->addMinutes(25),
        'scopes' => 'accounting.transactions',
        'tenant_id' => 'test-tenant-id',
    ]);

    Http::fake([
        'api.xero.com/api.xro/2.0/Invoices/'.$invoiceId => Http::response([
            'Type' => 'NotFound',
            'Message' => 'Invoice not found',
        ], 404),
    ]);

    Xero::invoices()->canEmail($invoiceId);
})->throws(Exception::class);
