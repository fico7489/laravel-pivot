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

        \Event::listen('eloquent.*', function ($model, $relation = null, $pivotIds = [], $pivotIdsAttributes = []) {
            $eventName = \Event::firing();
            if (strpos($eventName, 'eloquent.retrieved') !== 0) {
                self::$events[] = ['name' => $eventName, 'model' => $model, 'relation' => $relation, 'pivotIds' => $pivotIds, 'pivotIdsAttributes' => $pivotIdsAttributes];
            }
        });
    }

    private function startListening()
    {
        self::$events = [];
        return User::find(1);
    }

    public function test_attach_int()
    {
        $user = $this->startListening();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user->roles()->attach(1);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => []]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_attach_array()
    {
        $user = $this->startListening();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user->roles()->attach([1]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => []]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_attach_multiple_with_attributes()
    {
        $user = $this->startListening();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user->roles()->attach([1 => ['value' => 123], 2 => ['value' => 456]], ['value2' => 789]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1, 2]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => ['value' => 123, 'value2' => 789], 2 => ['value' => 456, 'value2' => 789]]);
        $this->assertEquals(2, count(self::$events));

        $this->assertEquals('123', \DB::table('role_user')->first()->value);
        $this->assertEquals('789', \DB::table('role_user')->first()->value2);
    }

    public function test_attach_model()
    {
        $user = $this->startListening();
        $role = Role::find(1);

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user->roles()->attach($role, ['value' => 'test']);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => ['value' => 'test']]);
        $this->assertEquals(2, count(self::$events));

        $this->assertEquals('test', \DB::table('role_user')->first()->value);
    }

    public function test_attach_collection()
    {
        $user = $this->startListening();
        $roles = Role::take(2)->get();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $user->roles()->attach($roles);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1, 2]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => [], 2 => []]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach()
    {
        $user = $this->startListening();
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach([2, 3]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [2, 3]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach_model()
    {
        $user = $this->startListening();
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $role = Role::find(1);
        $user->roles()->detach($role);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_detach_collection()
    {
        $user = $this->startListening();
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $roles = Role::take(2)->get();
        $user->roles()->detach($roles);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1, 2]);
        $this->assertEquals(2, count(self::$events));
    }

    public function test_update()
    {
        $user = $this->startListening();
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->updateExistingPivot(1, ['value' => 2]);

        $this->assertEquals(3, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotUpdating: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotUpdating: ' . User::class, 'name'));

        $this->assertEquals(self::$events[0]['pivotIds'], [1]);
        $this->assertEquals(self::$events[0]['pivotIdsAttributes'], [1 => ['value' => 2]]);
        $this->assertEquals(2, count(self::$events));

        $this->assertEquals(2, \DB::table('role_user')->first()->value);
    }
    
    public function test_sync()
    {
        $user = $this->startListening();
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

    public function test_sync_collection()
    {
        $user = $this->startListening();
        $user->roles()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $roles = Role::take(2)->get(); // [1, 2]
        $user->roles()->sync($roles);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttaching: '  . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotAttached: '   . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetaching: ' . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.pivotDetached: '  . User::class, 'name'));
        $this->assertEquals(4, count(self::$events));
    }

    public function test_sync_db_updates()
    {
        $user = $this->startListening();
        $user->roles()->sync([1 => ['value' => '123']]);
        $this->assertEquals(123, \DB::table('role_user')->first()->value);
    }

    public function test_standard_update()
    {
        $user = $this->startListening();
        
        $this->startListening();
        $user->update(['name' => 'different']);

        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.updating: '  . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.updated: '   . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.saving: '    . User::class, 'name'));
        $this->assertNotNull($this->get_from_array(self::$events, 'eloquent.saved: '     . User::class, 'name'));
        $this->assertEquals(4, count(self::$events));
    }

    public function test_relation_is_null()
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

        $eventName = 'eloquent.pivotAttaching: ' . User::class;
        $event = $this->get_from_array(self::$events, $eventName, 'name');

        $this->assertEquals('roles', $event['relation']);
        $this->assertTrue($event['model'] instanceof Model);
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
