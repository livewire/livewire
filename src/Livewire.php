<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Livewire\Testing\TestableLivewire test($name, $params = [])
 *
 * @see \Livewire\LivewireManager
 */
class Livewire extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'livewire';
    }
}
