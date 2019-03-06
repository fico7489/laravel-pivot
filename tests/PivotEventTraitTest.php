<?php

namespace Fico7489\Laravel\Pivot\Tests;

use Fico7489\Laravel\Pivot\Tests\Models\Tag;
use Fico7489\Laravel\Pivot\Tests\Models\Post;
use Fico7489\Laravel\Pivot\Tests\Models\Role;
use Fico7489\Laravel\Pivot\Tests\Models\User;
use Fico7489\Laravel\Pivot\Tests\Models\Video;
use Fico7489\Laravel\Pivot\Tests\Models\Seller;

class PivotEventTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
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

        $this->check_events(['eloquent.pivotAttaching: '.User::class, 'eloquent.pivotAttached: '.User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(1, 123, 0, 'value');
    }

    public function test_polymorphic_attach_int()
    {
        $this->startListening();
        $post = Post::find(1);
        $post->tags()->attach(1, ['value' => 123]);

        $this->check_events(['eloquent.pivotAttaching: '.Post::class, 'eloquent.pivotAttached: '.Post::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]], 'tags');
        $this->check_database(1, 123, 0, 'value', 'taggables');
    }

    public function test_attach_string()
    {
        $this->startListening();
        $user = User::find(1);
        $seller = Seller::first();
        $user->sellers()->attach($seller->id, ['value' => 123]);

        $this->check_events(['eloquent.pivotAttaching: '.User::class, 'eloquent.pivotAttached: '.User::class]);
        $this->check_variables(0, [$seller->id], [$seller->id => ['value' => 123]], 'sellers');
        $this->check_database(1, 123, 0, 'value', 'seller_user');
    }

    public function test_attach_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1 => ['value' => 123], 2 => ['value' => 456]], ['value2' => 789]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: '.User::class, 'eloquent.pivotAttached: '.User::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123, 'value2' => 789], 2 => ['value' => 456, 'value2' => 789]]);
        $this->check_database(2, 123);
        $this->check_database(2, 789, 0, 'value2');
    }

    public function test_polymorphic_attach_array()
    {
        $this->startListening();
        $video = Video::find(1);
        $video->tags()->attach([1 => ['value' => 123], 2 => ['value' => 456]], ['value2' => 789]);

        $this->assertEquals(2, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotAttaching: '.Video::class, 'eloquent.pivotAttached: '.Video::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123, 'value2' => 789], 2 => ['value' => 456, 'value2' => 789]], 'tags');
        $this->check_database(2, 123, 0, 'value', 'taggables');
        $this->check_database(2, 789, 0, 'value2', 'taggables');
    }

    public function test_attach_model()
    {
        $this->startListening();
        $user = User::find(1);
        $role = Role::find(1);
        $user->roles()->attach($role, ['value' => 123]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: '.User::class, 'eloquent.pivotAttached: '.User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(1, 123);
    }

    public function test_polymorphic_attach_model()
    {
        $this->startListening();
        $tag = Tag::find(1);
        $video = Video::find(1);
        $tag->videos()->attach($video, ['value' => 123]);

        $this->assertEquals(1, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotAttaching: '.Tag::class, 'eloquent.pivotAttached: '.Tag::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]], 'videos');
        $this->check_database(1, 123, 0, 'value', 'taggables');
    }

    public function test_attach_collection()
    {
        $this->startListening();
        $user = User::find(1);
        $roles = Role::take(2)->get();
        $user->roles()->attach($roles, ['value' => 123]);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotAttaching: '.User::class, 'eloquent.pivotAttached: '.User::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123], 2 => ['value' => 123]]);
        $this->check_database(2, 123);
        $this->check_database(2, 123, 1);
    }

    public function test_polymorphic_attach_collection()
    {
        $this->startListening();
        $post = Post::find(1);
        $tags = Tag::take(2)->get();
        $post->tags()->attach($tags, ['value' => 123]);

        $this->assertEquals(2, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotAttaching: '.Post::class, 'eloquent.pivotAttached: '.Post::class]);
        $this->check_variables(0, [1, 2], [1 => ['value' => 123], 2 => ['value' => 123]], 'tags');
        $this->check_database(2, 123, 0, 'value', 'taggables');
        $this->check_database(2, 123, 1, 'value', 'taggables');
    }

    public function test_detach_int()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach(2);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: '.User::class, 'eloquent.pivotDetached: '.User::class]);
        $this->check_variables(0, [2]);
    }

    public function test_polymorphic_detach_int()
    {
        $this->startListening();
        $video = Video::find(1);
        $video->tags()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('taggables')->count());

        $this->startListening();
        $video->tags()->detach(2);

        $this->assertEquals(2, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotDetaching: '.Video::class, 'eloquent.pivotDetached: '.Video::class]);
        $this->check_variables(0, [2], [], 'tags');
    }

    public function test_detach_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach([2, 3]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: '.User::class, 'eloquent.pivotDetached: '.User::class]);
        $this->check_variables(0, [2, 3]);
    }

    public function test_polymorphic_detach_array()
    {
        $this->startListening();
        $post = Post::find(1);
        $post->tags()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('taggables')->count());

        $this->startListening();
        $post->tags()->detach([2, 3]);

        $this->assertEquals(1, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotDetaching: '.Post::class, 'eloquent.pivotDetached: '.Post::class]);
        $this->check_variables(0, [2, 3], [], 'tags');
    }

    public function test_detach_model()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $role = Role::find(1);
        $user->roles()->detach($role);

        $this->assertEquals(2, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: '.User::class, 'eloquent.pivotDetached: '.User::class]);
        $this->check_variables(0, [1]);
    }

    public function test_polymorphic_detach_model()
    {
        $this->startListening();
        $post = Post::find(1);
        $video = Video::find(1);
        $post->tags()->attach([1, 2]);
        $video->tags()->attach([2]);
        $this->assertEquals(3, \DB::table('taggables')->count());

        $this->startListening();
        $tag = Tag::find(2);
        $tag->videos()->detach($video);

        $this->assertEquals(2, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotDetaching: '.Tag::class, 'eloquent.pivotDetached: '.Tag::class]);
        $this->check_variables(0, [1], [], 'videos');
    }

    public function test_detach_collection()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $roles = Role::take(2)->get();
        $user->roles()->detach($roles);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: '.User::class, 'eloquent.pivotDetached: '.User::class]);
        $this->check_variables(0, [1, 2]);
    }

    public function test_polymorphic_detach_collection()
    {
        $this->startListening();
        $post = Post::find(1);
        $post->tags()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('taggables')->count());

        $this->startListening();
        $tags = Tag::take(2)->get();
        $post->tags()->detach($tags);

        $this->assertEquals(1, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotDetaching: '.Post::class, 'eloquent.pivotDetached: '.Post::class]);
        $this->check_variables(0, [1, 2], [], 'tags');
    }

    public function test_detach_null()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);
        $this->assertEquals(3, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->detach();

        $this->assertEquals(0, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotDetaching: '.User::class, 'eloquent.pivotDetached: '.User::class]);
        $this->check_variables(0, [1, 2, 3]);
    }

    public function test_polymorphic_detach_null()
    {
        $this->startListening();
        $post = Post::find(1);
        $post->tags()->attach([1, 2]);
        $video = Video::find(2);
        $video->tags()->attach([2, 3]);
        $this->assertEquals(4, \DB::table('taggables')->count());

        $this->startListening();
        $post->tags()->detach();

        $this->assertEquals(2, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotDetaching: '.Post::class, 'eloquent.pivotDetached: '.Post::class]);
        $this->check_variables(0, [1, 2], [], 'tags');
    }

    public function test_update()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([1, 2, 3]);

        $this->startListening();
        $user->roles()->updateExistingPivot(1, ['value' => 123]);

        $this->assertEquals(3, \DB::table('role_user')->count());
        $this->check_events(['eloquent.pivotUpdating: '.User::class, 'eloquent.pivotUpdated: '.User::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]]);
        $this->check_database(3, 123, 0);
        $this->check_database(3, null, 2);
    }

    public function test_polymorphic_update()
    {
        $this->startListening();
        $video = Video::find(1);
        $video->tags()->attach([1, 2, 3]);

        $this->startListening();
        $video->tags()->updateExistingPivot(1, ['value' => 123]);

        $this->assertEquals(3, \DB::table('taggables')->count());
        $this->check_events(['eloquent.pivotUpdating: '.Video::class, 'eloquent.pivotUpdated: '.Video::class]);
        $this->check_variables(0, [1], [1 => ['value' => 123]], 'tags');
        $this->check_database(3, 123, 0, 'value', 'taggables');
        $this->check_database(3, null, 2, 'value', 'taggables');
    }

    public function test_sync_int()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync(1);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events([
            'eloquent.pivotDetaching: '.User::class,
            'eloquent.pivotDetached: '.User::class,
            'eloquent.pivotAttaching: '.User::class,
            'eloquent.pivotAttached: '.User::class,
        ]);
    }

    public function test_polymorphic_sync_int()
    {
        $this->startListening();
        $post = Post::find(1);
        $post->tags()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('taggables')->count());

        $this->startListening();
        $post->tags()->sync(1);

        $this->assertEquals(1, \DB::table('taggables')->count());
        $this->check_events([
            'eloquent.pivotDetaching: '.Post::class,
            'eloquent.pivotDetached: '.Post::class,
            'eloquent.pivotAttaching: '.Post::class,
            'eloquent.pivotAttached: '.Post::class,
        ]);
    }

    public function test_sync_array()
    {
        $this->startListening();
        $user = User::find(1);
        $user->roles()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('role_user')->count());

        $this->startListening();
        $user->roles()->sync([1]);

        $this->assertEquals(1, \DB::table('role_user')->count());
        $this->check_events([
            'eloquent.pivotDetaching: '.User::class,
            'eloquent.pivotDetached: '.User::class,
            'eloquent.pivotAttaching: '.User::class,
            'eloquent.pivotAttached: '.User::class,
        ]);
    }

    public function test_polymorphic_sync_array()
    {
        $this->startListening();
        $video = Video::find(1);
        $video->tags()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('taggables')->count());

        $this->startListening();
        $video->tags()->sync([1]);

        $this->assertEquals(1, \DB::table('taggables')->count());
        $this->check_events([
            'eloquent.pivotDetaching: '.Video::class,
            'eloquent.pivotDetached: '.Video::class,
            'eloquent.pivotAttaching: '.Video::class,
            'eloquent.pivotAttached: '.Video::class,
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
            'eloquent.pivotDetaching: '.User::class,
            'eloquent.pivotDetached: '.User::class,
            'eloquent.pivotAttaching: '.User::class,
            'eloquent.pivotAttached: '.User::class,
        ]);
        $this->assertEquals(4, count(self::$events));
    }

    public function test_polymorphic_sync_model()
    {
        $this->startListening();
        $video = Video::find(1);
        $video->tags()->attach([2, 3]);
        $this->assertEquals(2, \DB::table('taggables')->count());

        $this->startListening();
        $tag = Tag::find(1);
        $video->tags()->sync($tag);

        $this->assertEquals(1, \DB::table('taggables')->count());

        $this->check_events([
            'eloquent.pivotDetaching: '.Video::class,
            'eloquent.pivotDetached: '.Video::class,
            'eloquent.pivotAttaching: '.Video::class,
            'eloquent.pivotAttached: '.Video::class,
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
            'eloquent.pivotDetaching: '.User::class,
            'eloquent.pivotDetached: '.User::class,
            'eloquent.pivotAttaching: '.User::class,
            'eloquent.pivotAttached: '.User::class,
            'eloquent.pivotAttaching: '.User::class,
            'eloquent.pivotAttached: '.User::class,
        ]);
        $this->check_variables(0, [1, 2], []);
        $this->check_variables(2, [3], [3 => []]);
        $this->check_variables(4, [4], [4 => []]);
    }

    public function test_polymorphic_sync_collection()
    {
        $this->startListening();
        $tag = Tag::find(1);
        $tag->posts()->attach([1]);
        $tag->videos()->attach([2]);
        $this->assertEquals(2, \DB::table('taggables')->count());

        $this->startListening();
        $posts = Post::where('id', 2)->get();
        $tag->posts()->sync($posts);

        $this->check_events([
            'eloquent.pivotDetaching: '.Tag::class,
            'eloquent.pivotDetached: '.Tag::class,
            'eloquent.pivotAttaching: '.Tag::class,
            'eloquent.pivotAttached: '.Tag::class,
        ]);
        $this->check_variables(0, [1], [], 'posts');
        $this->check_variables(2, [2], [2 => []], 'posts');
    }

    public function test_standard_update()
    {
        $this->startListening();
        $user = User::find(1);

        $this->startListening();
        $user->update(['name' => 'different']);

        $this->check_events([
            'eloquent.saving: '.User::class,
            'eloquent.updating: '.User::class,
            'eloquent.updated: '.User::class,
            'eloquent.saved: '.User::class,
        ]);
    }

    public function test_relation_is_null()
    {
        $this->startListening();
        $user = User::find(1);
        $user->update(['name' => 'new_name']);

        $eventName = 'eloquent.updating: '.User::class;
        $this->check_variables(0, [], [], null);
    }

    private function check_events($events)
    {
        $i = 0;
        foreach ($events as $event) {
            $this->assertEquals(self::$events[$i]['name'], $event);
            ++$i;
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
