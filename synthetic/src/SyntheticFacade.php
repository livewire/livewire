<?php

namespace Synthetic;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Synthetic\SyntheticManager
 */
class SyntheticFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'synthetic';
    }
}
