<?php

namespace Livewire;

class LivewireBladeDirectives
{
    public static function livewire($expression)
    {
        return "<?php echo \Livewire\Livewire::mount({$expression})->dom; ?>";
    }
}
