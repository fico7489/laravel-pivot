<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\Role;
use Fico7489\Laravel\Pivot\Tests\Models\User;
use Illuminate\Database\Eloquent\Model;

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

        \Event::listen('eloquent.*', function ($model, $relation = null) {
            self::$events[] = ['name' => \Event::firing(), 'model' => $model, 'relation' => $relation];
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

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach_events()
    {
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->detach([1]);

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetached: ' . User::class, 'name'));
        $this->assertEquals(2, count(self::$events));
    }

    public function test_update_events()
    {
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->updateExistingPivot(1, ['value' => 2]);

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotUpdating: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotUpdated: ' . User::class, 'name'));
        $this->assertEquals(2, count(self::$events));
    }

    public function test_relation_null(){
        $user = User::find(1);

        $this->startListening();
        $user->update(['name' => 'new_name']);

        $eventName = 'eloquent.updating: ' . User::class;
        $event = $this->get_from_array(self::$events, $eventName, 'name');

        $this->assertNull($event['relation']);
        $this->assertTrue($event['model'] instanceof Model);
    }

    public function test_relation_not_null(){
        $user = User::find(1);

        $this->startListening();
        $user->roles()->sync([1, 2]);

        $eventName = 'eloquent.pivotAttaching: ' . User::class;
        $event = $this->get_from_array(self::$events, $eventName, 'name');

        $this->assertEquals('roles', $event['relation']);
        $this->assertTrue($event['model'] instanceof Model);
    }

    private function get_from_array($items, $value, $field)
    {
        foreach($items as $key => $item)
        {
            if ( $item[$field] === $value )
                return $item;
        }

        return false;
    }
}
