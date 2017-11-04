<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\Role;
use Fico7489\Laravel\Pivot\Tests\Models\User;

class PivotEventTraitTest extends TestCase
{
    static $events = [];

    public function setUp()
    {
        parent::setUp();

        User::create(['name' => 'example@example.com']);
        User::create(['name' => 'example2@example.com']);

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'customer']);

        \Event::listen('eloquent.*', function ($data) {
            self::$events[] = \Event::firing();
        });
    }

    private function startListening(){
        self::$events = [];
    }

    public function test_attach_events()
    {
        $this->startListening();

        $user = User::find(1);
        $user->roles()->attach([1]);

        $this->assertContains('eloquent.pivotAttaching: ' . User::class, self::$events);
        $this->assertContains('eloquent.pivotAttached: ' . User::class, self::$events);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach_events()
    {
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->detach([1]);

        $this->assertContains('eloquent.pivotDetaching: ' . User::class, self::$events);
        $this->assertContains('eloquent.pivotDetached: ' . User::class, self::$events);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_update_events()
    {
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->updateExistingPivot(1, ['value' => 2]);

        $this->assertContains('eloquent.pivotUpdating: ' . User::class, self::$events);
        $this->assertContains('eloquent.pivotUpdated: ' . User::class, self::$events);
        $this->assertEquals(2, count(self::$events));
    }
}
