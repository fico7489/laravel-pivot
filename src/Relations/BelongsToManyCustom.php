<?php

namespace Fico7489\Laravel\Pivot\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
        list($cleanId, $idAttributes) = $this->cleanIdAndAttributes($id, $attributes);

        $this->parent->fireModelEvent('pivotAttaching', true, $this->getRelationName(), $cleanId, $idAttributes);
        $status = parent::attach($id, $attributes, $touch);
        $this->parent->fireModelEvent('pivotAttached', false, $this->getRelationName(), $cleanId, $idAttributes);

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
        list($cleanId) = $this->cleanIdAndAttributes($ids, []);

        $this->parent->fireModelEvent('pivotDetaching', true, $this->getRelationName(), $cleanId);
        $status = parent::detach($ids, $touch);
        $this->parent->fireModelEvent('pivotDetached', false, $this->getRelationName(), $cleanId);

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
        list($cleanId, $idAttributes) = $this->cleanIdAndAttributes($id, $attributes);

        $this->parent->fireModelEvent('pivotUpdating', true, $this->getRelationName(), $cleanId, $idAttributes);
        $status = parent::updateExistingPivot($id, $attributes, $touch);
        $this->parent->fireModelEvent('pivotUpdated', false, $this->getRelationName(), $cleanId, $idAttributes);

        return $status;
    }

    /**
     * Cleans the Id and attributes
     * Returns an array with and array of ids and array of id => attributes
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @return array
     */
    private function cleanIdAndAttributes($id, $attributes = [])
    {
        $cleanId = [];
        $cleanIdAttributes = [];

        if ($id instanceof Model) {
            $cleanId = [$id->getKey()];
            $cleanIdAttributes[$id->getKey()] = $attributes;
            return [$cleanId, $cleanIdAttributes];
        }

        if ($id instanceof Collection) {
            $cleanId = $id->modelKeys();
            foreach ($cleanId as $value) {
                $cleanIdAttributes[$value] = $attributes;
            }
            return [$cleanId, $cleanIdAttributes];
        }

        if (is_array($id)) {
            foreach ($id as $key => $value) {
                if (is_array($value)) {
                    $cleanId[] = $key;
                    $cleanIdAttributes[$key] = array_merge($value, $attributes);
                } else {
                    $cleanId[] = $value;
                    $cleanIdAttributes[$value] = $attributes;
                }
            }
            return [$cleanId, $cleanIdAttributes];
        }

        $cleanId = [$id];
        $cleanIdAttributes[$id] = $attributes;
        
        return [$cleanId, $cleanIdAttributes];
    }
}
