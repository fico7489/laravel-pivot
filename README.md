# Laravel Pivot

This package introduces new eloquent events for sync(), attach(), detach() or updateExistingPivot() methods on  BelongsToMany relation.

## Laravel Problems

In Laravel events are not dispatched when BelongsToMany relation (pivot table) is updated with sync(), attach(), detach() or updateExistingPivot() methods, but this package will help with that.

## Version Compatibility

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| >= 5.5.0 | 3.* | yes | master
| < 5.5.0 | - | no | -

* you still can use inactive branches for laravel 5.4.x or older

## Install

1.Install package with composer
```
composer require fico7489/laravel-pivot
```
With this statement, a composer will install highest available package version for your current laravel version.

2.Use Fico7489\Laravel\Pivot\Traits\PivotEventTrait trait in your base model or only in particular models.

```php
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
pivotSyncing, pivotSynced,
pivotAttaching, pivotAttached
pivotDetaching, pivotDetached,
pivotUpdating, pivotUpdated
```

The best way to catch events is with this model functions: 

```php
public static function boot()
{
    parent::boot();

    static::pivotSyncing(function ($model, $relationName) {
        //
    });
    
    static::pivotSynced(function ($model, $relationName, $changes) {
        //
    });
    
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

You can also see those events here: 

```php
\Event::listen('eloquent.*', function ($eventName, array $data) {
    echo $eventName;  //e.g. 'eloquent.pivotAttached'
});
```

## Suported relations

**BelongsToMany**  and **MorphToMany**  

## Which events are dispatched and when they are dispatched

Four BelongsToMany methods dispatches events from this package: 

**attach()**  
Dispatches **one** **pivotAttaching** and **one** **pivotAttached** event.  
Even when more rows are added only **one** event is dispatched for all rows but in that case, you can see all changed row ids in the $pivotIds variable, and the changed row ids with attributes in the $pivotIdsAttributes variable.   

**detach()**  
Dispatches **one** **pivotDetaching** and **one** **pivotDetached** event.  
Even when more rows are deleted only **one** event is dispatched for all rows but in that case, you can see all changed row ids in the $pivotIds variable.   

**updateExistingPivot()**  
Dispatches **one** **pivotUpdating** and **one** **pivotUpdated** event.   
You can change only one row in the pivot table with updateExistingPivot.   

**sync()**  
Dispatches **one** **pivotSyncing** and **one** **pivotSynced** event.  
*How does it work:* The sync first detaches all associations and then attaches or updates new entries one by one.  
Whether a row was attached/detached/updated during sync only **one** event is dispatched for all rows but in that case, you can see all the attached/detached/updated rows in the $changes variables.

## Usage

We have three tables in database users(id, name), roles(id, name), role_user(user_id, role_id).
We have two models : 

```php

class User extends Model
{
    use PivotEventTrait;
    ....
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    static::pivotSynced(function ($model, $relationName, $changes) {
        echo 'pivotAttached';
        echo get_class($model);
        echo $relationName;
        print_r($changes);
    });
    
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

```php
class Role extends Model
{
    ....
```

### Attaching 

For attach() or detach() one event is dispatched for both pivot ids.

#### Attaching with int
Running this code 
```php
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
```php
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
```php
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
```php
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
```php
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


### Syncing:

For sync() method event is dispatched for each pivot row.

Running this code 
```php
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

### Detaching:

Running this code 
```php
$user = User::first();
$user->roles()->detach([1, 2]);
```
You will see this output
```
pivotDetached
App\Models\User
roles
[1, 2]
```

### Updating:

Running this code 
```php
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
