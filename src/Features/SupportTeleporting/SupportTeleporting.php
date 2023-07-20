<?php

namespace Livewire\Features\SupportTeleporting;

use Livewire\ComponentHook;

class SupportTeleporting extends ComponentHook
{
    static function provide()
    {
        app('livewire')->directive('teleport', function ($expression) {
            return '<template x-teleport="<?php echo e('.$expression.'); ?>">';
        });

        app('livewire')->directive('endteleport', function () {
            return '</template>';
        });
    }
}
