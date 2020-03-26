<?php

namespace Fico7489\Laravel\Pivot\Relations;

use Fico7489\Laravel\Pivot\Traits\FiresPivotEventsTrait;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class MorphToManyCustom extends MorphToMany
{
    use FiresPivotEventsTrait;
}
