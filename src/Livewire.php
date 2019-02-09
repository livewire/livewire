<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Livewire extends BaseFacade
{
    public static function getFacadeAccessor()
    {
        return 'livewire';
    }
}
