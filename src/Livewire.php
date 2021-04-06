<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void component($alias, $viewClass)
 * @method static \Livewire\Testing\TestableLivewire test($name, $params = [])
 * @method static \Livewire\LivewireManager actingAs($user, $driver = null)
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
