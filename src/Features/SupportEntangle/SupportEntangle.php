<?php

namespace Livewire\Features\SupportEntangle;

use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

class SupportEntangle extends ComponentHook
{
    public static function provide()
    {
        Blade::directive('entangle', function ($expression) {
            return <<<EOT
            <?php if ((object) ({$expression}) instanceof \Livewire\WireDirective) : ?>\$wire.entangle('{{ {$expression}->value() }}'){{ {$expression}->hasModifier('live') ? '.live' : '' }}<?php else : ?>\$wire.entangle('{{ {$expression} }}')<?php endif; ?>
            EOT;
        });
    }
}
