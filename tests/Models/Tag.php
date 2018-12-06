<?php

namespace crishellco\Laravel\Pivot\Tests\Models;

use crishellco\Laravel\Pivot\Traits\PivotEventTrait;

class Tag extends BaseModel
{
    use PivotEventTrait;

    protected $table = 'videos';

    protected $fillable = ['name'];

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function videos()
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}
