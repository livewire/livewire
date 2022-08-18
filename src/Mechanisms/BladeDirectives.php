<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Blade;

class BladeDirectives
{
    public function __invoke()
    {
        Blade::directive('livewireScripts', [static::class, 'livewireScripts']);
        Blade::directive('livewireStyles', [static::class, 'livewireStyles']);
    }

    public static function livewireStyles($expression)
    {
        return '{!! \Livewire\Assets::styles('.$expression.') !!}';
    }

    public static function livewireScripts($expression)
    {
        return '{!! \Livewire\Assets::scripts('.$expression.') !!}';
    }
}
