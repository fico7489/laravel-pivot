<?php

namespace Fico7489\Laravel\Pivot\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
    
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
