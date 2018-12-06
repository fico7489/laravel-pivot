<?php

namespace crishellco\Laravel\Pivot\Tests\Models;

use crishellco\Laravel\Pivot\Traits\PivotEventTrait;

class Video extends BaseModel
{
    use PivotEventTrait;

    protected $table = 'videos';

    protected $fillable = ['name'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
