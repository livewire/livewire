<?php

namespace Livewire\Features\SupportThisDirective;

use Illuminate\Support\Facades\Blade;

class SupportThisDirective
{
    function boot()
    {
        Blade::directive('this', function () {
            return "window.livewire.find('{{ \$_instance->getId() }}')";
        });
    }
}
