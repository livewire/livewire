<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

class Livewire extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'livewire';
    }
}
