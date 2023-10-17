<?php

namespace Livewire\Features\SupportEntangle;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportEntangle extends ComponentHook
{
    public static function provide()
    {
        Blade::directive('entangle', function ($expression) {
            return <<<EOT
            <?php if ((object) ({$expression}) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('{{ \$__livewire->getId() }}').entangle('{{ {$expression}->value() }}'){{ {$expression}->hasModifier('live') ? '.live' : '' }}<?php else : ?>window.Livewire.find('{{ \$__livewire->getId() }}').entangle('{{ {$expression} }}')<?php endif; ?>
            EOT;
        });
    }
}
