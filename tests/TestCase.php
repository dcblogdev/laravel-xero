<?php

namespace Dcblogdev\Xero\Tests;

use CreateXeroTokensTable;
use Dcblogdev\Xero\XeroServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            XeroServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        require_once 'src/database/migrations/create_xero_tokens_table.php';

        // run the up() method of that migration class
        (new CreateXeroTokensTable)->up();
    }
}
