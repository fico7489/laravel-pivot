<?php

namespace Fico7489\Laravel\Pivot\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCustom extends BelongsToMany
{
    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        $this->parent->fireModelEvent('pivotAttaching', true, $this->getRelationName());
        $status = parent::attach($id, $attributes, $touch);
        $this->parent->fireModelEvent('pivotAttached', false, $this->getRelationName());

        return $status;
    }

    /**
     * Detach models from the relationship.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($ids = [], $touch = true)
    {
        $this->parent->fireModelEvent('pivotDetaching', true, $this->getRelationName());
        $status = parent::detach($ids, $touch);
        $this->parent->fireModelEvent('pivotDetached', false, $this->getRelationName());

        return $status;
    }

    /**
     * Update an existing pivot record on the table.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return int
     */
    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        $this->parent->fireModelEvent('pivotUpdating', true, $this->getRelationName());
        $status = parent::updateExistingPivot($id, $attributes, $touch);
        $this->parent->fireModelEvent('pivotUpdated', false, $this->getRelationName());

        return $status;
    }
}
