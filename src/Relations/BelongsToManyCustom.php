<?php

namespace Fico7489\Laravel\Pivot\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyCustom extends BelongsToMany
{

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $ids
     * @param  bool   $detaching
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        $this->parent->fireModelEvent('updating');
        $status = parent::sync($ids, $detaching);
        $this->parent->fireModelEvent('updated');

        return $status;
    }

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
        $this->parent->fireModelEvent('updating');
        $status = parent::attach($id, $attributes, $touch);
        $this->parent->fireModelEvent('updated');

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
        $this->parent->fireModelEvent('updating');
        $status = parent::detach($ids, $touch);
        $this->parent->fireModelEvent('updated');

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
        $this->parent->fireModelEvent('updating');
        $status = parent::updateExistingPivot($id, $attributes, $touch);
        $this->parent->fireModelEvent('updated');

        return $status;
    }
}
