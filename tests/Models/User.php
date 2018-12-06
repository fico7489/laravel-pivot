<?php

namespace crishellco\Laravel\Pivot\Tests\Models;

use crishellco\Laravel\Pivot\Traits\PivotEventTrait;

class User extends BaseModel
{
    use PivotEventTrait;

    protected $table = 'users';

    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['value']);
    }

    public function sellers()
    {
        return $this->belongsToMany(Seller::class)
            ->withPivot(['value']);
    }
}
