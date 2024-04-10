<?php

namespace Livewire\Features\SupportTeleporting;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportTeleporting extends ComponentHook
{
    static function provide()
    {
        Blade::directive('teleport', function ($expression) {
            return '<template x-teleport="<?php echo e('.$expression.'); ?>">';
        });

        Blade::directive('endteleport', function () {
            return '</template>';
        });
    }
}
