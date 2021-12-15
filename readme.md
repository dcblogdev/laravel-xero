
[![Latest Version on Packagist](https://img.shields.io/packagist/v/dcblogdev/laravel-xero.svg?style=flat-square)](https://packagist.org/packages/dcblogdev/laravel-xero)
[![Total Downloads](https://img.shields.io/packagist/dt/dcblogdev/laravel-xero.svg?style=flat-square)](https://packagist.org/packages/dcblogdev/laravel-xero)

![Logo](https://repository-images.githubusercontent.com/317929912/1e40a180-49c1-11eb-893d-af9c59d29ad5)

Laravel package for working with the Xero API

Watch a video walkthrough https://www.youtube.com/watch?v=sORX2z-AH1k

Xero API documentation can be found at:
https://developer.xero.com/documentation/

Before you can integrate with Xero you will need to create an app, go to https://developer.xero.com/myapps to register a new app.

For the grant type select, Auth code (web app)

For OAuth 2.0 redirect URI enter the full URL you want to use for connection to Xero from your application such as https://domain.com/xero/connect

# Install

You can install the package via composer:

```
composer require dcblogdev/laravel-xero
```

# Config

You can publish the config file with:

```
php artisan vendor:publish --provider="Dcblogdev\Xero\XeroServiceProvider" --tag="config"
```

# Migration

You can publish the migration with:

```
php artisan vendor:publish --provider="Dcblogdev\Xero\XeroServiceProvider" --tag="migrations"
```

After the migration has been published you can create the tokens tables by running the migration:

```
php artisan migrate
```

.ENV Configuration
Ensure you've set the following in your .env file:

```
XERO_CLIENT_ID=
XERO_CLIENT_SECRET=
XERO_REDIRECT_URL=https://domain.com/xero/connect
XERO_LANDING_URL=https://domain.com/xero
XERO_WEBHOOK_KEY=
```

# Middleware

To restrict access to routes only to authenticated users there is a middleware route called XeroAuthenticated

Add XeroAuthenticated to routes to ensure the user is authenticated:

```php
Route::group(['middleware' => ['web', 'XeroAuthenticated'], function()
```

To access token model reference this ORM model:

```php
use Dcblogdev\Xero\Models\XeroToken;
```

# Usage

A routes example:

```php
Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('xero', function(){

        if (! Xero::isConnected()) {
            return redirect('xero/connect');
        } else {
            //display your tenant name
            return Xero::getTenantName();
        }

    });

    Route::get('xero/connect', function(){
        return Xero::connect();
    });
});
```

Or using a middleware route, if the user does not have a token then automatically redirect to get authenticated:

```php
Route::group(['middleware' => ['web', 'XeroAuthenticated']], function(){
    Route::get('xero', function(){
        return Xero::getTenantName();
    });
});
```

```php
Route::get('xero/connect', function(){
    return Xero::connect();
});
```

Once authenticated you can call Xero:: with the following verbs:

```php
Xero::get($endpoint, $array = [])
Xero::post($endpoint, $array = [])
Xero::put($endpoint, $array = [])
Xero::patch($endpoint, $array = [])
Xero::delete($endpoint, $array = [])
```php

The second param of array is not always required, its requirement is determined from the endpoint being called, see the API documentation for more details.

These expect the API endpoints to be passed, the URL https://api.xero.com/api.xro/2.0/ is provided, only endpoints after this should be used ie:

```php
Xero::get('contacts')
```


## Change log

Please see the [changelog][3] for more information on what has changed recently.

## Contributing

Contributions are welcome and will be fully credited.

Contributions are accepted via Pull Requests on [Github][4].

## Pull Requests

- **Document any change in behaviour** - Make sure the `readme.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0][5]. Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Security

If you discover any security related issues, please email dave@dcblog.dev email instead of using the issue tracker.

## License

license. Please see the [license file][6] for more information.

[3]:    changelog.md
[4]:    https://github.com/dcblogdev/laravel-xero
[5]:    http://semver.org/
[6]:    license.md
