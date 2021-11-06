<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self register(...$args)
 * @method static array properties()
 * @method static bool has($classToCheck)
 * @method static bool hasNot($classToCheck)
 * @method static mixed dehyrdate($value)
 * @method static mixed hydrate($instance, $property, $value)
 *
 * @see \Livewire\LivewirePropertyManager
 */
class LivewireProperty extends Facade
{
    public static function getFacadeAccessor()
    {
        return LivewirePropertyManager::class;
    }
}
