<?php

namespace Dcblogdev\Xero;

use Dcblogdev\Xero\Actions\formatQueryStringsAction;
use Dcblogdev\Xero\Actions\StoreTokenAction;
use Dcblogdev\Xero\Actions\tokenExpiredAction;
use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Resources\Contacts;
use Dcblogdev\Xero\Resources\CreditNotes;
use Dcblogdev\Xero\Resources\Invoices;
use Dcblogdev\Xero\Resources\Webhooks;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * @method static array get (string $endpoint, array $params = [])
 * @method static array put (string $endpoint, array $params = [])
 * @method static array post (string $endpoint, array $params = [])
 * @method static array patch (string $endpoint, array $params = [])
 * @method static array delete (string $endpoint, array $params = [])
 */
class Xero
{
    protected static string $baseUrl = 'https://api.xero.com/api.xro/2.0/';

    protected static string $authorizeUrl = 'https://login.xero.com/identity/connect/authorize';

    protected static string $connectionUrl = 'https://api.xero.com/connections';

    protected static string $tokenUrl = 'https://identity.xero.com/connect/token';

    protected static string $revokeUrl = 'https://identity.xero.com/connect/revocation';

    protected string $tenant_id = '';

    public function setTenantId(string $tenant_id): void
    {
        $this->tenant_id = $tenant_id;
    }

    public function contacts(): Contacts
    {
        return new Contacts;
    }

    public function creditnotes(): CreditNotes
    {
        return new CreditNotes;
    }

    public function invoices(): Invoices
    {
        return new Invoices;
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks;
    }

    public function isTokenValid(): bool
    {
        $token = $this->getTokenData();

        if ($token === null) {
            return false;
        }

        $now = now()->addMinutes(5);

        if ($token->expires < $now) {
            return false;
        }

        return true;
    }


    public function isConnected(): bool
    {
        return ! ($this->getTokenData() === null);
    }

    public function disconnect(): void
    {
        try {
            $token = $this->getTokenData();

            Http::withHeaders([
                'authorization' => 'Basic '.base64_encode(config('xero.clientId').':'.config('xero.clientSecret')),
            ])
                ->asForm()
                ->post(self::$revokeUrl, [
                    'token' => $token->refresh_token,
                ])->throw();

            $token->delete();
        } catch (Exception $e) {
            throw new RuntimeException('error getting tenant: '.$e->getMessage());
        }
    }

    /**
     * Make a connection or return a token where it's valid
     *
     * @return RedirectResponse|Application|Redirector
     *
     * @throws Exception
     */
    public function connect()
    {
        //when no code param redirect to Microsoft
        if (request()->has('code')) {
            // With the authorization code, we can retrieve access tokens and other data.
            try {
                $params = [
                    'grant_type' => 'authorization_code',
                    'code' => request('code'),
                    'redirect_uri' => config('xero.redirectUri'),
                ];

                $result = $this->sendPost(self::$tokenUrl, $params);

                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer '.$result['access_token'],
                    ])
                        ->acceptJson()
                        ->get(self::$connectionUrl)
                        ->throw()
                        ->json();

                    foreach ($response as $tenant) {
                        $tenantData = [
                            'auth_event_id' => $tenant['authEventId'],
                            'tenant_id' => $tenant['tenantId'],
                            'tenant_type' => $tenant['tenantType'],
                            'tenant_name' => $tenant['tenantName'],
                            'created_date_utc' => $tenant['createdDateUtc'],
                            'updated_date_utc' => $tenant['updatedDateUtc'],
                        ];

                        app(StoreTokenAction::class)($result, $tenantData, $tenant['tenantId']);
                    }
                } catch (Exception $e) {
                    throw new Exception('Error getting tenant: '.$e->getMessage());
                }

                return redirect(config('xero.landingUri'));
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        $url = self::$authorizeUrl.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('xero.clientId'),
            'redirect_uri' => config('xero.redirectUri'),
            'scope' => config('xero.scopes'),
        ]);

        return redirect()->away($url);
    }

    public function getTokenData(): ?XeroToken
    {
        if ($this->tenant_id) {
            $token = XeroToken::where('tenant_id', '=', $this->tenant_id)->first();
        } else {
            $token = XeroToken::first();
        }

        if ($token && config('xero.encrypt')) {
            try {
                $access_token = Crypt::decryptString($token->access_token);
            } catch (DecryptException $e) {
                $access_token = $token->access_token;
            }

            // Split them as a refresh token may not exist...
            try {
                $refresh_token = Crypt::decryptString($token->refresh_token);
            } catch (DecryptException $e) {
                $refresh_token = $token->refresh_token;
            }

            $token->access_token = $access_token;
            $token->refresh_token = $refresh_token;
        }

        return $token;
    }

    /**
     * @throws Exception
     */
    public function getAccessToken(bool $redirectWhenNotConnected = true): string
    {
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token, $redirectWhenNotConnected);

        $now = now()->addMinutes(5);

        if ($token->expires < $now) {
            return $this->renewExpiringToken($token);
        }

        return $token->access_token;
    }

    /**
     * @throws Exception
     */
    public function renewExpiringToken(XeroToken $token): string
    {
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'redirect_uri' => config('xero.redirectUri'),
        ];

        $result = $this->sendPost(self::$tokenUrl, $params);

        app(tokenExpiredAction::class)($result, $token);
        app(StoreTokenAction::class)($result, ['tenant_id' => $token->tenant_id], $this->tenant_id);

        return $result['access_token'];
    }

    public function getTenantId(): string
    {
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token);

        return $token->tenant_id;
    }

    public function getTenantName(): string
    {
        //use id if passed otherwise use logged-in user
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token);

        // Token is still valid, just return it
        return $token->tenant_name;
    }

    /**
     * __call catches all requests when no found method is requested
     *
     * @param  string  $function  - the verb to execute
     * @param  array  $args  - array of arguments
     * @return array
     *
     * @throws Exception
     */
    public function __call(string $function, array $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path = $args[0] ?? '';
        $data = $args[1] ?? [];
        $raw = $args[2] ?? false;
        $accept = $args[3] ?? 'application/json';
        $headers = $args[4] ?? []; // Add a new line for custom headers

        if (in_array($function, $options)) {
            return $this->guzzle($function, $path, $data, $raw, $accept, $headers);
        } else {
            //request verb is not in the $options array
            throw new RuntimeException($function.' is not a valid HTTP Verb');
        }
    }

    protected function redirectIfNoToken(string $token, bool $redirectWhenNotConnected = true)
    {
        // Check if tokens exist otherwise run the oauth request
        if (! $this->isConnected() && $redirectWhenNotConnected === true) {
            return redirect()->away(config('xero.redirectUri'));
        }

        return false;
    }

    /**
     * run guzzle to process requested url
     *
     * @throws Exception
     */
    protected function guzzle(string $type, string $request, array $data = [], bool $raw = false, string $accept = 'application/json', array $headers = []): array
    {
        if ($data === []) {
            $data = null;
        }

        try {
            $response = Http::withToken($this->getAccessToken())
                ->withHeaders(array_merge(['Xero-tenant-id' => $this->getTenantId()], $headers))
                ->accept($accept)
                ->$type(self::$baseUrl.$request, $data)
                ->throw();

            return [
                'body' => $raw ? $response->body() : $response->json(),
                'headers' => $response->getHeaders(),
            ];
        } catch (RequestException $e) {
            $response = json_decode($e->response->body());
            throw new Exception($response->Detail ?? "Type: $response?->Type Message: $response?->Message Error Number: $response?->ErrorNumber");
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    protected static function sendPost(string $url, array $params)
    {
        try {
            $response = Http::withHeaders([
                'authorization' => 'Basic '.base64_encode(config('xero.clientId').':'.config('xero.clientSecret')),
            ])
                ->asForm()
                ->acceptJson()
                ->post($url, $params);

            return $response->json();

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function formatQueryStrings(array $params): string
    {
        return app(formatQueryStringsAction::class)($params);
    }
}
