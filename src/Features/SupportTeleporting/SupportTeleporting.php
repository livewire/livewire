<?php

namespace Livewire\Features\SupportTeleporting;

class SupportTeleporting
{
    function boot()
    {
        app('livewire')->directive('teleport', function ($expression) {
            return '<template x-teleport="<?php echo e('.$expression.'); ?>">';
        });

        app('livewire')->directive('endteleport', function () {
            return '</template>';
        });
    }
}
