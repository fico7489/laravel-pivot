# Laravel Pivot

With this package Laravel fires updating|updated events on base model when BelongsToMany methods are being called.
Covers sync(), attach(), detach() and updateExistingPivot() functions.

# How to use

1.First : install with composer "fico7489/laravel-pivot"

2.Second : Use Fico7489\Laravel\Pivot\Traits\PivotEventTrait trait in your base model or only in some models.

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


License
----

MIT


**Free Software, Hell Yeah!**