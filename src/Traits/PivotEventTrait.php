<?php

namespace Fico7489\Laravel\Pivot\Traits;

trait PivotEventTrait
{
    use ExtendBelongsToManyTrait;
    use ExtendFireModelEventTrait;

    public static function pivotAttaching($callback, $priority = 0)
    {
        static::registerModelEvent('pivotAttaching', $callback, $priority);
    }

    public static function pivotAttached($callback, $priority = 0)
    {
        static::registerModelEvent('pivotAttached', $callback, $priority);
    }

    public static function pivotDetaching($callback, $priority = 0)
    {
        static::registerModelEvent('pivotDetaching', $callback, $priority);
    }

    public static function pivotDetached($callback, $priority = 0)
    {
        static::registerModelEvent('pivotDetached', $callback, $priority);
    }

    public static function pivotUpdating($callback, $priority = 0)
    {
        static::registerModelEvent('pivotUpdating', $callback, $priority);
    }

    public static function pivotUpdated($callback, $priority = 0)
    {
        static::registerModelEvent('pivotUpdated', $callback, $priority);
    }

    /**
     * Get the observable event names.
     *
     * @return array
     */
    public function getObservableEvents()
    {
        return array_merge(
            [
                'creating', 'created', 'updating', 'updated',
                'deleting', 'deleted', 'saving', 'saved',
                'restoring', 'restored',
                'pivotAttaching', 'pivotAttached',
                'pivotDetaching', 'pivotDetached',
                'pivotUpdating', 'pivotUpdated',
            ],
            $this->observables
        );
    }
}
