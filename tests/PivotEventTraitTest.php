<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\Role;
use Fico7489\Laravel\Pivot\Tests\Models\Seller;
use Fico7489\Laravel\Pivot\Tests\Models\User;

class PivotEventTraitTest extends TestCase
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

        $this->assertEquals(0, \DB::table('role_user')->count());
        $this->assertEquals(0, \DB::table('seller_user')->count());

        \Event::listen('eloquent.*', function ($eventName, array $data) {
            if (strpos($eventName, 'eloquent.retrieved') !== 0) {
                self::$events[] = ['name' => $eventName, 'model' => $data['model'], 'relation' => $data['relation'], 'pivotIds' => $data['pivotIds'], 'pivotIdsAttributes' => $data['pivotIdsAttributes']];
            }
        });
    }

    private function startListening()
    {
        self::$events = [];
    }

    public function test_attach_int()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach(1, ['value' => 123]);

        $this->check_events(['eloquent.pivotAttaching: ' . User::class, 'eloquent.pivotAttached: ' . User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(1, 123, 0, 'value');
    }

    public function test_attach_string()
    {
        $this->startListening();
        $user = User::find(1);
        $seller = Seller::first();
        $user->sellers()->attach($seller->id, ['value' => 123]);

        $this->check_events(['eloquent.pivotAttaching: ' . User::class, 'eloquent.pivotAttached: ' . User::class]);
        $this->check_variables(0, [$seller->id], [$seller->id => ['value' => 123]], 'sellers');
        $this->check_database(1, 123, 0, 'value', 'seller_user');
    }

    public function test_attach_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1 => ['value' => 123], 2 => ['value' => 456]], ['value2' => 789]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: ' . User::class, 'eloquent.pivotAttached: ' . User::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123, 'value2' => 789], 2 => ['value' => 456, 'value2' => 789]]);
        $this->check_database(2, 123);
        $this->check_database(2, 789, 0, 'value2');
    }

    public function test_attach_model()
    {
        $this->startListening();
        $user = User::find(1);
        $role = Role::find(1);
        $user->roles()->attach($role, ['value' => 123]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: ' . User::class, 'eloquent.pivotAttached: ' . User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(1, 123);
    }

    public function test_attach_collection()
    {
        $this->startListening();
        $user = User::find(1);
        $roles = Role::take(2)->get();
        $user->roles()->attach($roles, ['value' => 123]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: ' . User::class, 'eloquent.pivotAttached: ' . User::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123], 2 => ['value' => 123]]);
        $this->check_database(2, 123);
        $this->check_database(2, 123, 1);
    }

    public function test_detach_int()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach(2);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: ' . User::class, 'eloquent.pivotDetached: ' . User::class]);
        $this->check_variables(0, [2]);
    }

    public function test_detach_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach([2, 3]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: ' . User::class, 'eloquent.pivotDetached: ' . User::class]);
        $this->check_variables(0, [2, 3]);
    }

    public function test_detach_model()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $role = Role::find(1);
        $user->roles()->detach($role);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: ' . User::class, 'eloquent.pivotDetached: ' . User::class]);
        $this->check_variables(0, [1]);
    }

    public function test_detach_collection()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $roles = Role::take(2)->get();
        $user->roles()->detach($roles);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: ' . User::class, 'eloquent.pivotDetached: ' . User::class]);
        $this->check_variables(0, [1, 2]);
    }

    public function test_detach_null()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: ' . User::class, 'eloquent.pivotDetached: ' . User::class]);
        $this->check_variables(0, [1, 2, 3]);
    }

    public function test_update()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2 ,3]);

        $this->startListening();
        $user->roles()->updateExistingPivot(1, ['value' => 123]);

        $this->assertEquals(3, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotUpdating: ' . User::class, 'eloquent.pivotUpdated: ' . User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(3, 123, 0);
        $this->check_database(3, null, 2);
    }

    public function test_sync_int()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([2 ,3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync(1);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events([
            'eloquent.pivotDetaching: ' . User::class,
            'eloquent.pivotDetached: ' . User::class,
            'eloquent.pivotAttaching: ' . User::class,
            'eloquent.pivotAttached: ' . User::class,
        ]);
    }
    
    public function test_sync_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([2 ,3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync([1]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events([
            'eloquent.pivotDetaching: ' . User::class,
            'eloquent.pivotDetached: ' . User::class,
            'eloquent.pivotAttaching: ' . User::class,
            'eloquent.pivotAttached: ' . User::class,
        ]);
    }
    
    public function test_sync_model()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $role = Role::find(1);
        $user->roles()->sync($role);

        $this->check_events([
            'eloquent.pivotDetaching: ' . User::class,
            'eloquent.pivotDetached: ' . User::class,
            'eloquent.pivotAttaching: ' . User::class,
            'eloquent.pivotAttached: ' . User::class,
        ]);
        $this->assertEquals(4, count(self::$events));
    }

    public function test_sync_collection()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $roles = Role::whereIn('id', [3, 4])->get();
        $user->roles()->sync($roles);

        $this->check_events([
            'eloquent.pivotDetaching: ' . User::class,
            'eloquent.pivotDetached: ' . User::class,
            'eloquent.pivotAttaching: ' . User::class,
            'eloquent.pivotAttached: ' . User::class,
            'eloquent.pivotAttaching: ' . User::class,
            'eloquent.pivotAttached: ' . User::class,
        ]);
        $this->check_variables(0, [1, 2], []);
        $this->check_variables(2, [3], [3 => []]);
        $this->check_variables(4, [4], [4 => []]);
    }

    public function test_standard_update()
    {
        $this->startListening();
        $user = User::find(1);
        
        $this->startListening();
        $user->update(['name' => 'different']);

        $this->check_events([
            'eloquent.saving: ' . User::class,
            'eloquent.updating: ' . User::class,
            'eloquent.updated: ' . User::class,
            'eloquent.saved: ' . User::class,
        ]);
    }

    public function test_relation_is_null()
    {
        $this->startListening();
        $user = User::find(1);
        $user->update(['name' => 'new_name']);

        $eventName = 'eloquent.updating: ' . User::class;
        $this->check_variables(0, [], [], null);
    }

    private function check_events($events)
    {
        $i = 0;
        foreach ($events as $event) {
            $this->assertEquals(self::$events[$i]['name'], $event);
            $i++;
        }
        $this->assertEquals(count($events), count(self::$events));
    }

    private function check_variables($number, $ids, $idsAttributes = [], $relation = 'roles')
    {
        $this->assertEquals(self::$events[$number]['pivotIds'], $ids);
        $this->assertEquals(self::$events[$number]['pivotIdsAttributes'], $idsAttributes);
        $this->assertEquals(self::$events[$number]['relation'], $relation);
    }

    private function check_database($count, $value, $number = 0, $attribute = 'value', $table = 'role_user')
    {
        $this->assertEquals($value, \DB::table($table)->get()->get($number)->$attribute);
        $this->assertEquals($count, \DB::table($table)->count());
    }
}
