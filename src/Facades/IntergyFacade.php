<?php namespace Intergy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 */
class Intergy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'intergy';
    }
}
