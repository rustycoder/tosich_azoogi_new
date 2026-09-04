<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $this->isolateTestingDatabase($app);

        return $app;
    }

    /**
     * phpunit.xml already sets sqlite/:memory:, but a cached config file
     * bakes in the local MySQL database and RefreshDatabase will wipe it.
     */
    private function isolateTestingDatabase(Application $app): void
    {
        $app['env'] = 'testing';
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.prefix', '');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('airtable.api_key', '');
        $app['config']->set('airtable.base_id', '');

        foreach (['mysql', 'mariadb', 'pgsql', 'sqlsrv'] as $connection) {
            if ($app['config']->has("database.connections.{$connection}")) {
                $app['config']->set("database.connections.{$connection}.url", null);
                $app['config']->set("database.connections.{$connection}.database", 'testing_forbidden');
            }
        }

        $app->make('db')->purge();
        $app->make('db')->setDefaultConnection('sqlite');

        $connection = $app->make('db')->getDefaultConnection();
        $driver = $app['config']->get("database.connections.{$connection}.driver");
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($driver !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException('Tests must use sqlite :memory: so they cannot wipe local data.');
        }
    }
}
