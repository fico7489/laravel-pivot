<?php

namespace Fico7489\Laravel\Pivot\Traits;

trait PivotEventTrait
{
    use ExtendBelongsToManyTrait;
    use ExtendMorphToManyTrait;
    use ExtendFireModelEventTrait;

    public function initializePivotEventTrait()
    {
        $this->addObservableEvents([
            'pivotAttaching', 'pivotAttached',
            'pivotDetaching', 'pivotDetached',
            'pivotUpdating', 'pivotUpdated',
        ]);
    }

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
}
