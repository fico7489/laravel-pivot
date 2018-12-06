<?php

namespace crishellco\Laravel\Pivot\Tests;

use crishellco\Laravel\Pivot\Tests\Models\Tag;
use crishellco\Laravel\Pivot\Tests\Models\Post;
use crishellco\Laravel\Pivot\Tests\Models\Role;
use crishellco\Laravel\Pivot\Tests\Models\User;
use crishellco\Laravel\Pivot\Tests\Models\Video;
use crishellco\Laravel\Pivot\Tests\Models\Seller;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public static $events = [];

    public function setUp()
    {
        parent::setUp();

        User::create(['name' => 'example@example.com']);
        User::create(['name' => 'example2@example.com']);

        Seller::create(['name' => 'seller 1']);

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'driver']);

        Post::create(['name' => 'Learn Laravel in 30 days']);
        Post::create(['name' => 'Vue.js for Dummies']);

        Video::create(['name' => 'Laravel from Scratch']);
        Video::create(['name' => 'ES2015 Fundamentals']);

        Tag::create(['name' => 'technology']);
        Tag::create(['name' => 'laravel']);
        Tag::create(['name' => 'java-script']);

        $this->assertEquals(0, \DB::table('role_user')->count());
        $this->assertEquals(0, \DB::table('seller_user')->count());
        $this->assertEquals(0, \DB::table('taggables')->count());

        \Event::listen('eloquent.*', function ($eventName, array $data) {
            if (0 !== strpos($eventName, 'eloquent.retrieved')) {
                self::$events[] = array_merge([$eventName], $data);
            }
        });
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
