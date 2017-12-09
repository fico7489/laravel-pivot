# Laravel Pivot

This package introduces new eloquent events for sync(), attach(), detach() or updateExistingPivot() methods on  BelongsToMany relation.

## Laravel Problems

In Laravel events are not dispatched when BelongsToMany relation (pivot table) is updated with sync(), attach(), detach() or updateExistingPivot() methods, but this package will help with that.

## Version Compatibility

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| 5.5.* | 2.1.* | yes | master
| 5.4.* | 2.0.* | yes | 2.0
| 5.3.* | 1.3.* | yes | 1.3
| 5.2.* | 1.2.* | yes | 1.2
| <5.2 | - | no |

## Install

1.Install package with composer
```
composer require fico7489/laravel-pivot:"*"
```
With this statement, a composer will install highest available package version for your current laravel version.

2.Use Fico7489\Laravel\Pivot\Traits\PivotEventTrait trait in your base model or only in particular models.

```
...
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use PivotEventTrait;
...
```

and that's it, enjoy.

## New eloquent events

You can check all eloquent events here:  https://laravel.com/docs/5.5/eloquent#events) 

New events are :

```
pivotAttaching, pivotAttached
pivotDetaching, pivotDetached,
pivotUpdating, pivotUpdated
```

The best way to catch events is with this model functions : 

```
public static function boot()
{
    parent::boot();

    static::pivotAttaching(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        //
    });
    
    static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        //
    });
    
    static::pivotDetaching(function ($model, $relationName, $pivotIds) {
        //
    });

    static::pivotDetached(function ($model, $relationName, $pivotIds) {
        //
    });
    
    static::pivotUpdating(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        //
    });
    
    static::pivotUpdated(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        //
    });
    
    static::updating(function ($model) {
        //this is how we catch standard eloquent events
    });
}
```

You can also see those events here : 

```
\Event::listen('eloquent.*', function ($eventName, array $data) {
    echo $eventName;  //e.g. 'eloquent.pivotAttached'
});
```

## Which events are dispatched and when they are dispatched

Four BelongsToMany methods dispatches events from this package : 

**attach()** -> dispatches only **one** pivotAttaching and pivotAttached event. 
Even when more rows are added only **one** event is dispatched but in that case you can see all changed row ids in $pivotIds variable, and the changed row ids related attributes in $pivotIdsAttributes variable.

**detach()** -> dispatches **one** pivotDetaching and pivotDetached event.
Even when more rows are deleted only **one** event is dispatched but in that case you can see all changed row ids in $pivotIds variable.

**updateExistingPivot()** -> dispatches only one pivotUpdating and pivotUpdated event.
You can change only one row in pivot table with updateExistingPivot.

**sync()** -> dispatches pivotAttaching, pivotAttached, pivotDetaching and pivotDetached events **more times**, depend on how many rows are added in pivot table. E.g. when you call sync() if two rows are added and one is deleted two pivotAttaching(pivotAttached) events and one pivotDetaching(pivotDetached) event will be dispatched. 
If sync() is called but rows are not added or deleted events are not dispatched.


## Usage

We have three tables in database users(id, name), roles(id, name), role_user(user_id, role_id).
We have two models : 

```
...
class User extends Model
{
    use PivotEventTrait;
    ....
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        echo 'pivotAttached';
        echo get_class($model);
        echo $relationName;
        print_r($pivotIds);
        print_r($pivotIdsAttributes);
    });
    
    static::pivotUpdated(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
        echo 'pivotUpdated';
        echo get_class($model);
        echo $relationName;
        print_r($pivotIds);
        print_r($pivotIdsAttributes);
    });
    
    static::pivotDetached(function ($model, $relationName, $pivotIds) {
        echo 'pivotDetached';
        echo get_class($model);
        echo $relationName;
        print_r($pivotIds);
    });
```

```
...
class Role extends Model
{
    ....
```

### Attaching 

For attach() or detach() one event is dispatched for both pivot ids.

#### Attaching with int
Running this code 
```
$user = User::first();
$user->roles()->attach(1);
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1]
[1 => []]
```


#### Attaching with array
Running this code 
```
$user = User::first();
$user->roles()->attach([1]);
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1]
[1 => []]
```


#### Attaching with model
Running this code 
```
$user = User::first();
$user->roles()->attach(Role::first());
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1]
[1 => []]
```


#### Attaching with collection
Running this code 
```
$user = User::first();
$user->roles()->attach(Role::get());
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1, 2]
[1 => [], 2 => []]
```


#### Attaching with array (id => attributes)
Running this code 
```
$user = User::first();
$user->roles()->attach([1, 2 => ['attribute' => 'test']], ['attribute2' => 'test2']);
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1, 2]
[1 => [], 2 => ['attribute' => 'test', 'attribute2' => 'test2']]
```


### Syncing

For sync() method event is dispatched for each pivot row.

Running this code 
```
$user = User::first();
$user->roles()->sync([1, 2]);
```

You will see this output

```
pivotAttached
App\Models\User
roles
[1]
[1 => []]

pivotAttached
App\Models\User
roles
[2]
[2 => []]
```

### Detaching

Running this code 
```
$user = User::first();
$user->roles()->detach([1, 2]);
```
You will see this output
```
pivotAttached
App\Models\User
roles
[1, 2]
```

### Updating

Running this code 
```
$user = User::first();
$user->roles()->updateExistingPivot(1, ['attribute' => 'test']);
```
You will see this output
```
pivotUpdated
App\Models\User
roles
[1]
[1 => ['attribute' => 'test']]
```

License
----

MIT


**Free Software, Hell Yeah!**