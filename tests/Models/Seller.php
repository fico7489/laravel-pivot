<?php

namespace Fico7489\Laravel\Pivot\Tests\Models;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

class Seller extends BaseModel
{
    use PivotEventTrait;

    public $incrementing = false;

    protected $table = 'sellers';

    protected $fillable = ['name'];

    /**
     * Boot the String Id for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = str_random(16);
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['value']);
    }
}
