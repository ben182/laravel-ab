<?php

namespace Ben182\LaravelAb;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ben182\LaravelAb\Skeleton\SkeletonClass
 */
class LaravelAbFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-ab';
    }
}
