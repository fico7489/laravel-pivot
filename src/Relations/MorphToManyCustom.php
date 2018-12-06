<?php

namespace crishellco\Laravel\Pivot\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use crishellco\Laravel\Pivot\Traits\FiresPivotEventsTrait;

class MorphToManyCustom extends MorphToMany
{
    use FiresPivotEventsTrait;
}
