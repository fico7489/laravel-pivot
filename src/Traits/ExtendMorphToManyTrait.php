<?php

namespace Fico7489\Laravel\Pivot\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Fico7489\Laravel\Pivot\Relations\MorphToManyCustom;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait ExtendMorphToManyTrait
{
    /**
     * Instantiate a new MorphToMany relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model   $parent
     * @param string                                $name
     * @param string                                $table
     * @param string                                $foreignPivotKey
     * @param string                                $relatedPivotKey
     * @param string                                $parentKey
     * @param string                                $relatedKey
     * @param string                                $relationName
     * @param bool                                  $inverseE
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
                                      $relatedPivotKey, $parentKey, $relatedKey,
                                      $relationName = null, $inverse = false)
    {
        return new MorphToManyCustom($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse);
    }
}
