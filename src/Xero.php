<?php

namespace Dcblogdev\Xero;

use Dcblogdev\Xero\Models\XeroToken;
use Dcblogdev\Xero\Resources\Contacts;
use Dcblogdev\Xero\Resources\Invoices;
use Dcblogdev\Xero\Resources\Webhooks;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class Xero
{
    protected static $baseUrl       = 'https://api.xero.com/api.xro/2.0/';
    protected static $authorizeUrl  = 'https://login.xero.com/identity/connect/authorize';
    protected static $connectionUrl = 'https://api.xero.com/connections';
    protected static $tokenUrl      = 'https://identity.xero.com/connect/token';
    protected static $revokeUrl     = 'https://identity.xero.com/connect/revocation';

    protected $tenant_id;

    public function setTenantId($tenant_id = 0)
    {
        $this->tenant_id = $tenant_id;
    }

    public function contacts()
    {
        return new Contacts();
    }

    public function invoices()
    {
        return new Invoices();
    }

    public function webhooks()
    {
        return new Webhooks();
    }

    public function isConnected()
    {
        return $this->getTokenData() === null ? false : true;
    }

    public function disconnect()
    {
        try {
            $token  = $this->getTokenData();

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $token->access_token,
            ])
            ->asForm()
            ->post(self::$revokeUrl, [
                'token' => $token->access_token,
            ])->throw();

            $token->delete();
        } catch (Exception $e) {
            throw new Exception('error getting tenant: ' . $e->getMessage());
        }
    }

    /**
     * Make a connection or return a token where it's valid
     * @return mixed
     */
    public function connect()
    {
        //when no code param redirect to Microsoft
        if (! request()->has('code')) {
            $url = self::$authorizeUrl . '?' . http_build_query([
                'response_type' => 'code',
                'client_id'     => config('xero.clientId'),
                'redirect_uri'  => config('xero.redirectUri'),
                'scope'         => config('xero.scopes')
            ]);

            return redirect()->away($url);
        } elseif (request()->has('code')) {
            // With the authorization code, we can retrieve access tokens and other data.
            try {
                $params = [
                    'grant_type'   => 'authorization_code',
                    'code'         => request('code'),
                    'redirect_uri' => config('xero.redirectUri')
                ];

                $resultCode = $this->dopost(self::$tokenUrl, $params);

                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $resultCode['access_token'],
                    ])->acceptJson()->get(self::$connectionUrl)->throw()->json();

                    foreach ($response as $tenant) {
                        $tenantData = [
                            'auth_event_id'    => $tenant['authEventId'],
                            'tenant_id'        => $tenant['tenantId'],
                            'tenant_type'      => $tenant['tenantType'],
                            'tenant_name'      => $tenant['tenantName'],
                            'created_date_utc' => $tenant['createdDateUtc'],
                            'updated_date_utc' => $tenant['updatedDateUtc']
                        ];

                        $this->storeToken($resultCode, $tenantData);
                    }
                } catch (Exception $e) {
                    throw new Exception('error getting tenant: ' . $e->getMessage());
                }

                return redirect(config('xero.landingUri'));
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * @param  $id  - integar id of user
     * @return object
     */
    public function getTokenData()
    {
        if ($this->tenant_id) {
            return XeroToken::where('id', '=', $this->tenant_id)->first();
        }

        return XeroToken::first();
    }

    /**
     * Return authenticated access token or request new token when expired
     * @param  $id integer - id of the user
     * @return string
     */
    public function getAccessToken($redirectWhenNotConnected = true)
    {
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token, $redirectWhenNotConnected);

        // Check if token is expired / expiring in the next 5 minutes
        // Get current time + 5 minutes (to allow for time differences)
        // using now so that Carbon::faking works
        $now = now()->addMinutes(5);

        if ($token->expires->isBefore($now)) {
            // Token is expired (or very close to it) so let's refresh

            $params = [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $token->refresh_token,
                'redirect_uri'  => config('xero.redirectUri')
            ];

            $resultCode = $this->dopost(self::$tokenUrl, $params);

            // Store the new values
            $this->storeToken($resultCode);

            return $resultCode['access_token'];
        }

        // Token is still valid, just return it
        return $token->access_token;
    }

    public function getTenantId()
    {
        //use id if passed otherwise use logged in user
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token);

        // Token is still valid, just return it
        return $token->tenant_id;
    }

    public function getTenantName()
    {
        //use id if passed otherwise use logged in user
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token);

        // Token is still valid, just return it
        return $token->tenant_name;
    }

    /**
     * __call catches all requests when no found method is requested
     * @param  string  $function  - the verb to execute
     * @param  array  $args  - array of arguments
     * @return array
     */
    public function __call($function, $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path    = (isset($args[0])) ? $args[0] : '';
        $data    = (isset($args[1])) ? $args[1] : [];
        $raw     = (isset($args[2])) ? $args[2] : false;

        if (in_array($function, $options)) {
            return $this->guzzle($function, $path, $data, $raw);
        } else {
            //request verb is not in the $options array
            throw new Exception($function . ' is not a valid HTTP Verb');
        }
    }

    protected function redirectIfNoToken($token, $redirectWhenNotConnected = true)
    {
        // Check if tokens exist otherwise run the oauth request
        if (! $this->isConnected() && $redirectWhenNotConnected === true) {
            return redirect()->away(config('xero.redirectUri'));
        }
    }

    /**
     * Store token
     * @param  $token array
     * @param  $tentantData array|mixed
     * @return object
     */
    protected function storeToken($token, $tenantData = null)
    {
        $data = [
            'id_token'      => $token['id_token'],
            'access_token'  => $token['access_token'],
            'expires_in'    => $token['expires_in'],
            'token_type'    => $token['token_type'],
            'refresh_token' => $token['refresh_token'],
            'scopes'        => $token['scope']
        ];

        if ($this->tenant_id) {
            $where = ['id' => $this->tenant_id];
        } elseif ($tenantData !== null) {
            $data  = array_merge($data, $tenantData);
            $where = ['tenant_id' => $data['tenant_id']];
        } else {
            $where = ['id' => 1];
        }

        return XeroToken::updateOrCreate($where, $data);
    }

    /**
     * run guzzle to process requested url
     * @param  string  $type
     * @param  string  $request
     * @param  array  $data
     * @param  bool  $raw
     * @return array
     */
    protected function guzzle($type, $request, $data = [], $raw = false)
    {
        try {
            $response = Http::withToken($this->getAccessToken())->withHeaders([
                'Xero-tenant-id' => $this->getTenantId(),
            ])->acceptJson()->$type(self::$baseUrl . $request, $data)->throw();

            return [
                'body'    => $response->json(),
                'headers' => $response->getHeaders()
            ];
        } catch (RequestException $e) {
            throw new Exception($e->response->getBody()->getContents());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected static function dopost($url, $params)
    {
        try {
            $response = Http::withHeaders([
                'authorization' => "Basic " . base64_encode(config('xero.clientId') . ":" . config('xero.clientSecret'))
            ])->asForm()->acceptJson()->post($url, $params);

            return $response->json();
        } catch (Exception $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }
}
