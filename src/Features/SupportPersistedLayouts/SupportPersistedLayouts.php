<?php

namespace Livewire\Features\SupportPersistedLayouts;

use Illuminate\Support\Facades\Blade;

class SupportPersistedLayouts
{
    function boot()
    {
        Blade::directive('persist', function ($expression) {
            return '<div x-navigate:persist="<?php echo '.$expression.'; ?>">';
        });

        Blade::directive('endpersist', function () {
            return '</div>';
        });
    }
}
