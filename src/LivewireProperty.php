<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self register(...$args)
 * @method static array properties()
 * @method static array all()
 * @method static mixed get($class)
 * @method static bool has($class)
 * @method static bool exists($class)
 * @method static bool hasNot($class)
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
