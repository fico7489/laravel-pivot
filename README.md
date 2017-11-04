# Laravel Pivot

This package introduces new eloquent events for changes on BelongsToMany relation.

# Laravel versions

| Laravel Version | Package Tag | Supported |
|-----------------|-------------|-----------|
| 5.5.x | 1.5.x | yes |
| 5.4.x | 1.4.x | yes |
| 5.3.x | 1.3.x | yes |
| 5.2.x | 1.2.x | yes |
| <5.2 | - | no |

# How to use

1.Install package with composer
```
composer require fico7489/laravel-pivot:"~1.*"
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
pivotUpdating, pivotUpdated,
```

Best way to catch them is with model functions : 

```
public static function boot()
{
    parent::boot();

    static::pivotAttaching(function ($model, $relation) {
        //here you also know relation name
    });
    
    static::pivotAttached(function ($model, $relation) {
        //
    });
    
    static::pivotDetaching(function ($model, $relation) {
        //
    });

    static::pivotDetached(function ($model, $relation) {
        //
    });
    
    static::pivotUpdating(function ($model, $relation) {
        //
    });
    
    static::pivotUpdated(function ($model, $relation) {
        //
    });
    
    static::updating(function ($model) {
        //this is how we catch standard eloquent events
    });
}
```

You can also listen this events like other eloqent events by this way:

```
\Event::listen('eloquent.*', function ($model, $relation = null) {
    $eventName = \Event::firing();
});
```
# When events are fired

Four BelongsToMany methods fire this events : 

attach() -> fires only pivotAttaching and pivotAttached
detach() -> fires only pivotDetaching and pivotDetached
updateExistingPivot() -> fires only pivotUpdating and pivotUpdated
sync() -> fires pivotAttaching, pivotAttached, pivotDetaching and pivotDetached

License
----

MIT


**Free Software, Hell Yeah!**