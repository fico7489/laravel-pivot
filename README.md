# Laravel Pivot

This package introduces new eloquent events for changes on pivot table.

# Laravel versions

| Laravel Version | Package Tag | Supported |
|-----------------|-------------|-----------|
| 5.4.x | 2.0.x | yes |
| 5.3.x | 1.3.x | yes |
| 5.2.x | 1.2.x | yes |
| <5.2 | - | no |

*Support for laravel 5.4 and 5.5 coming soon

# Laravel Problems

Calling sync(), attach(), detach() or updateExistingPivot() on BelongsToMany relation does not fire any events, here this package jumps in.

# How to use

1.Install package with composer
```
composer require fico7489/laravel-pivot:"1.2.*"
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

# Eloquent events

You can check all eloquent events here:  https://laravel.com/docs/5.5/eloquent#events) 

New events are :

```
pivotAttaching, pivotAttached
pivotDetaching, pivotDetached,
pivotUpdating, pivotUpdated
```

Best way to catch events is with this model functions : 

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

You can also listen this events like other eloqent events by this way:

```
\Event::listen('eloquent.*', function ($model, $relation = null, $pivotIds = []) {
    $eventName = \Event::firing();
});
```
# When events are fired

Four BelongsToMany methods fires events from this package : 

* attach() -> fires only pivotAttaching and pivotAttached
* detach() -> fires only pivotDetaching and pivotDetached
* updateExistingPivot() -> fires only pivotUpdating and pivotUpdated
* sync() -> fires pivotAttaching, pivotAttached, pivotDetaching and pivotDetached

# One real example

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
        echo get_class($model);
        echo get_class($relationName);
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
$user = User::first();
$user->roles()->sync([1, 2]);
```

You will see this output

```
App\Models\User
roles
[1, 2]
```

License
----

MIT


**Free Software, Hell Yeah!**