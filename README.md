# Laravel Pivot

This package introduces new eloquent events for sync(), attach(), detach() or updateExistingPivot() methods on  BelongsToMany relation.

## Laravel versions

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| 5.5.x | 2.1.x | yes | master
| 5.4.x | 2.0.x | yes | 2.0
| 5.3.x | 1.3.x | yes | 1.3
| 5.2.x | 1.2.x | yes | 1.2
| <5.2 | - | no |

## Laravel Problems

In Laravel events are not dispatched when BelongsToMany relation (pivot table) is updated with sync(), attach(), detach() or updateExistingPivot() methods, but this package will help with that.

## How to use

1.Install package with composer
```
composer require fico7489/laravel-pivot:"2.1.*"
```
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

## Eloquent events

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

    static::pivotAttaching(function ($model, $relationName, $pivotIds) {
        //
    });
    
    static::pivotAttached(function ($model, $relationName, $pivotIds) {
        //
    });
    
    static::pivotDetaching(function ($model, $relationName, $pivotIds) {
        //
    });

    static::pivotDetached(function ($model, $relationName, $pivotIds) {
        //
    });
    
    static::pivotUpdating(function ($model, $relationName, $pivotIds) {
        //
    });
    
    static::pivotUpdated(function ($model, $relationName, $pivotIds) {
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
Even when more rows are added only **one** event is dispatched but in that case you can see all changed row ids in $pivotIds variable.

**detach()** -> dispatches **one** pivotDetaching and pivotDetached event.
Even when more rows are deleted only **one** event is dispatched but in that case you can see all changed row ids in $pivotIds variable.

**updateExistingPivot()** -> dispatches only one pivotUpdating and pivotUpdated event.
You can change only one row in pivot table with updateExistingPivot.

**sync()** -> dispatches pivotAttaching, pivotAttached, pivotDetaching and pivotDetached events **more times**, depend on how many rows are added in pivot table. E.g. when you call sync() if two rows are added and one is deleted two pivotAttaching(pivotAttached) events and one pivotDetaching(pivotDetached) event will be dispatched. 
If sync() is called but rows are not added or deleted events are not dispatched.


## See some action

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
    
    static::pivotAttached(function ($model, $relationName, $pivotIds) {
        echo 'pivotAttached';
        echo get_class($model);
        echo $relationName;
        print_r($pivotIds);
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

Running this code 
```
$user = User::first();           //assuming that pivot table is empty
$user->roles()->attach([1, 2]);
```

You will see this output

```
pivotAttached
App\Models\User
roles
[1, 2]
```
For attach() or detach() one event is dispatched for both pivot ids.

Running this code 
```
$user = User::first();           //assuming that pivot table is empty
$user->roles()->sync([1, 2]);
```

You will see this output

```
pivotAttached
App\Models\User
roles
[1]

pivotAttached
App\Models\User
roles
[2]
```

For sync() method event is dispatched for each pivot ids.

License
----

MIT


**Free Software, Hell Yeah!**