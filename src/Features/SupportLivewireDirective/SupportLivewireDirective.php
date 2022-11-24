<?php

namespace Livewire\Features\SupportLivewireDirective;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\Mechanisms\RenderComponent;

class SupportLivewireDirective
{
    function boot()
    {
        Blade::directive('livewire', function ($expression) {
            return RenderComponent::livewire($expression);
        });
    }
}
