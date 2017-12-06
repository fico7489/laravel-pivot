<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\Role;
use Fico7489\Laravel\Pivot\Tests\Models\User;
use Illuminate\Database\Eloquent\Model;

class PivotEventTraitTest extends TestCase
{
    public static $events = [];

    public function setUp()
    {
        parent::setUp();

        User::create(['name' => 'example@example.com']);
        User::create(['name' => 'example2@example.com']);

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'customer']);
        
        \Event::listen('eloquent.*', function ($eventName, array $data) {
            if (strpos($eventName, 'eloquent.retrieved') !== 0) {
                self::$events[] = ['name' => $eventName, 'model' => $data['model'], 'relation' => $data['relation'], 'pivotIds' => $data['pivotIds']];
            }
        });
    }

    private function startListening()
    {
        self::$events = [];
    }

    public function test_attach_events()
    {
        $this->startListening();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user = User::find(1);
        $user->roles()->attach([1, 2]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));
        
        $pivotIds = self::$events[0]['pivotIds'];
        $this->assertEquals($pivotIds, [1, 2]);
        
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach_events()
    {
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach([2, 3]);
        $this->assertEquals(1, \DB::table('role_user')->count());

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetached: ' . User::class, 'name'));
        
        $pivotIds = self::$events[0]['pivotIds'];
        $this->assertEquals($pivotIds, [2, 3]);
        
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
        
        $pivotIds = self::$events[0]['pivotIds'];
        $this->assertEquals($pivotIds, [1]);
        
        $this->assertEquals(2, count(self::$events));
    }
    
    public function test_sync_events()
    {
        $user = User::find(1);
        $user->roles()->attach([2 ,3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync([1]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: '  . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: '   . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetached: '  . User::class, 'name'));
        $this->assertEquals(4, count(self::$events));
    }
    
    public function test_standard_update_event()
    {
        $user = User::find(1);
        
        $this->startListening();
        $user->update(['name' => 'different']);

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.updating: '  . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.updated: '   . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.saving: '    . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.saved: '     . User::class, 'name'));
        $this->assertEquals(4, count(self::$events));
    }

    public function test_relation_null()
    {
        $user = User::find(1);

        $this->startListening();
        $user->update(['name' => 'new_name']);

        $eventName = 'eloquent.updating: ' . User::class;
        $event = $this->get_from_array(self::$events, $eventName, 'name');

        $this->assertNull($event['relation']);
        $this->assertTrue($event['model'] instanceof Model);
    }

    public function test_relation_not_null()
    {
        $user = User::find(1);
        $this->assertEquals(0, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync([1, 2]);
        $pivotIds  = self::$events[0]['pivotIds'];
        $pivotIds2 = self::$events[2]['pivotIds'];

        $eventName = 'eloquent.pivotAttaching: ' . User::class;
        $event = $this->get_from_array(self::$events, $eventName, 'name');

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertEquals('roles', $event['relation']);
        $this->assertTrue($event['model'] instanceof Model);
        $this->assertEquals(1, count($pivotIds));
        $this->assertEquals(1, $pivotIds[0]);
        $this->assertEquals(1, count($pivotIds2));
        $this->assertEquals(2, $pivotIds2[0]);
    }

    private function get_from_array($items, $value, $field)
    {
        foreach ($items as $key => $item) {
            if ($item[$field] === $value) {
                return $item;
            }
        }

        return null;
    }
}
