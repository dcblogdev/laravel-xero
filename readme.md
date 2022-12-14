
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

# Multi-tenancy

To set the tenant call `setTenantId` and pass in your tenant_id

Once set all calls will use the set tenant.

```php
setTenantId($tenant_id)
```

# Commands

## Refresh Token

When using Xero as a background process, tokens will need to be renwed, to automate this process use the command:

```
php artisan xero:keep-alive
```

This will refresh the token when its due to expire.
Its recommended to add this to a schedule ie inside `App\Console\Kernal.php` add the command to a schedule.

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('xero:keep-alive')->everyFiveMinutes();
}
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
```

The second param of array is not always required, its requirement is determined from the endpoint being called, see the API documentation for more details.

These expect the API endpoints to be passed, the URL https://api.xero.com/api.xro/2.0/ is provided, only endpoints after this should be used ie:

```php
Xero::get('contacts')
```

# Is Connected

Anytime you need to check if Xero is authenticated you can call a ->isConnected method. The method returns a boolean.

To do an action when a Xero is not connected can be done like this:

```php
if (! Xero::isConnected()) {
    return redirect('xero/connect');
}
```

# Disconnect
To disconnect from Xero call a ->disconnect() method.

The disconnect will send a POST request to Xero to revoke the connection, in addition, the stored token will be deleted.

```php
Xero::disconnect();
```

Typically you only want to run this is there is a connection, so it makes sense to wrap this in a ->isConnected() check:

```php
if (Xero::isConnected()) {
    Xero::disconnect();
}
```

# Contacts
Xero provides a clean way of working with Xero contacts.

To work with contacts first call ->contacts() followed by a method.

```php
Xero::contacts()
```

## List Contacts
To list contacts call the contacts()->get() method.

Xero docs for listing contacts - https://developer.xero.com/documentation/api/contacts#GET

The optional parameters are $page and $where. $page defaults to 1 which means being back on the first page, for additional pages enter higher page numbers until there are no pages left to return. The API does not offer a count to determine how many pages there are.

```php
Xero::contacts()->get(int $page = 1, string $where = null)
```

$where allows for filter options to be passed, the most common filters have been optimised to ensure performance across organisations of all sizes. We recommend you restrict your filtering to the following optimised parameters.

Filter by name:

```php
Xero::contacts()->get(1, 'Name="ABC limited"')
```

Filter by email:

```php
Xero::contacts()->get(1, 'EmailAddress="email@example.com"')
```

Filter by account number:

```php
Xero::contacts()->get(1, 'AccountNumber="ABC-100"')
```

## View Contact
To view a single contact a find method can be called passing in the contact id

```php
Xero::contacts()->find(string $contactId)
```

## Create Contacts
To create a contact call a store method passing in an array of contact data:

See https://developer.xero.com/documentation/api/contacts#POST for the array contents specifications

```php
Xero::contacts()->store($data)
```

## Update Contact

To update a contact 2 params are required the contact Id and an array of data to be updated:

See https://developer.xero.com/documentation/api/contacts#POST for details on the fields that can be updated.

```php
Xero::contacts()->update($contactId, $data);
```

# Invoices
Xero provides a clean way of working with Xero invoices.

To work with invoices first call ->invocies() followed by a method.

```php
Xero::invoices()
```

## List Invoices

To list invoices call the invoices()->get() method.

Xero docs for listing invoices - https://developer.xero.com/documentation/api/invoices#get

The optional parameters are $page and $where. $page defaults to 1 which means being back on the first page, for additional pages enter higher page numbers until there are no pages left to return. The API does not offer a count to determine how many pages there are.

```php
Xero::invoices()->get(int $page = 1, string $where = null)
```

$where allows for filter options to be passed, the most common filters have been optimised to ensure performance across organisations of all sizes. We recommend you restrict your filtering to the following optimised parameters.

Filter by status:

```php
Xero::invoices()->get(1, 'Status="AUTHORISED"')
```

Filter by contact id:

```php
Xero::invoices()->get(1, 'Contact.ContactID=guid("96988e67-ecf9-466d-bfbf-0afa1725a649")')
```

Filter by contact number

```php
Xero::invoices()->get(1, 'Contact.ContactNumber="ID001"')
```

Filter by reference

```php
Xero::invoices()->get(1, 'Reference="INV-0001"')
```

Filter by date

```php
Xero::invoices()->get(1, 'Date=DateTime(2020, 01, 01)')
```

Filter by type

```php
Xero::invoices()->get(1, 'Type="ACCREC"')
```

## View Invoice
To view a single invoice a find method can be called passing in the invoice id

```php
Xero::invoices()->find(string $invoiceId)
```

## Online Invoice
For invoices created that have a status of either Submitted, Authorised, or Paid an online invoice can be seen by calling onlineUrl passing in the invoiceId

```php
Xero::invoices()->onlineUrl($invoiceId)
```

## Create Invoice
To create a invoice call a store method passing in an array of invoice data:

See https://developer.xero.com/documentation/api/invoices#post  for the array contents specifications

```php
Xero::invoices()->store($data)
```

## Update Invoice
To update an invoice 2 params are required the invoice Id and an array of data to be updated:

See https://developer.xero.com/documentation/api/invoices#post for details on the fields that can be updated.

```php
Xero::invoices()->update($invoiceId, $data);
```

## PDF resources
Some Xero resources support being downloaded as PDF. To do this you can:

```php
$pdfInvoice = Xero::get("invoices/{$invoiceId}", null, true, 'application/pdf');
$pdfInvoice['body']; // will be the pdf as a pdf string for you to write out to storage etc.
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
