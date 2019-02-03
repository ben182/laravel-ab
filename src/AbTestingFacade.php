<?php

namespace Ben182\AbTesting;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ben182\AbTesting\Skeleton\SkeletonClass
 */
class AbTestingFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ab-testing';
    }
}
