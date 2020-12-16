
# Laravel Xero

A Laravel package for working with Xero API.

## Register Xero App

Before you can integrate with Xero you will need to create an app, go to https://developer.xero.com/myapps to register a new app.

For the grant type select Auth code (web app)
For OAuth 2.0 redirect URI enter the full URL you want to use for connection to Xero from your application such as `https://domain.com/xero/connect` 

Take note of the Client id and secret they are needed in .env see below.

## Installation

You can install the package via composer:

```
composer require dcblogdev/laravel-xero
```

## Config

You can publish the config file with:

```
php artisan vendor:publish --provider="Dcblogdev\Xero\XeroServiceProvider" --tag="config"
```

## Migration
You can publish the migration with:

```
php artisan vendor:publish --provider="Dcblogdev\Xero\XeroServiceProvider" --tag="migrations"
```

After the migration has been published you can create the tokens tables by running the migration:

```
php artisan migrate
```

## .ENV Configuration
Ensure you've set the following in your .env file:

```
XERO_CLIENT_ID=
XERO_CLIENT_SECRET=
XERO_REDIRECT_URL=https://domain.com/xero/connect
XERO_LANDING_URL=https://domain.com/xero
XERO_WEBHOOK_KEY=
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