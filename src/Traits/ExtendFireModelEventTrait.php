<?php

namespace Fico7489\Laravel\Pivot\Traits;

trait ExtendFireModelEventTrait
{
    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  bool  $halt
     * @return mixed
     */
    public function fireModelEvent($event, $halt = true, $relationName = null, $ids = [], $idsAttributes = [])
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        // We will append the names of the class to the event to distinguish it from
        // other model events that are fired, allowing us to listen on each model
        // event set individually instead of catching event for all the models.
        $event = "eloquent.{$event}: ".static::class;

        $method = $halt ? 'until' : 'fire';

        $payload = ['model' => $this, 'relation' => $relationName, 'pivotIds' => $ids, 'pivotIdsAttributes' => $idsAttributes];

        return static::$dispatcher->$method($event, $payload);
    }
}
