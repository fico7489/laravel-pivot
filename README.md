# Laravel Pivot

With this package Laravel fires updating|updated events on base model when BelongsToMany methods are being called.
Covers sync(), attach(), detach() and updateExistingPivot() methods.

# Laravel versions

| Laravel Version | Package Tag | Supported |
|-----------------|-------------|-----------|
| 5.5.x | 1.x | yes |
| 5.4.x | 1.x | yes |
| 5.3.x | 1.x | yes |
| 5.2.x | 1.x | yes |
| <5.2 | - | no |

# How to use

1.Install package with composer
```
composer require fico7489/laravel-pivot:"~1.0"
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

Eloquent have many events (see more https://laravel.com/docs/5.5/eloquent#events) including 
updating -> before the model will be updated
updated -> after the model is updated

If your User model looks like this : 

```
...
class class User extends BaseModel
{
    ...
    public static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            echo 'updating first_name';
        });

        static::updating(function ($user) {
            echo 'updating';
        });
    }
...
```

And then if you run this : 

```
$user = User::find(1);
$user->update(['first_name' => 'changed']);
```

You will see this output : 

```
updating
updating
```

But if User model have relation roles() and if you run sync  on this BelongsToMany relation:

```
$user = User::find(1);
$user->roles()->sync([1, 2, 3]);
```

The any event is not fired on User, RoleUser or Role model. Here this package jumps in and if you put Trait (Fico7489\Laravel\Pivot\Traits\PivotEventTrait) in your model updating and updated events will be fired in that case.

Some people want some other events here e.g. 'beforeSync', 'afterSync' but this package use 'updating' and 'updated' events.

License
----

MIT


**Free Software, Hell Yeah!**