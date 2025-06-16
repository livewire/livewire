<?php

namespace Livewire\V4\Islands;

use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

class SupportIslands extends ComponentHook
{
    public function boot()
    {
        Blade::directive('island', function ($expression) {
            // If there's no expression or it's empty, generate a random name
            if (empty(trim($expression))) {
                $randomName = "'" . uniqid('island_') . "'";
                $expression = $randomName;
            }

            return "<?php if (isset(\$_instance)) echo \$_instance->island({$expression}, fromBladeDirective: true); ?>";
        });
    }

    public function hydrate($memo)
    {
        if (! is_array($memo)) return;

        if (array_key_exists('islands', $memo)) {
            // Here we would handle island data from the memo if needed
        }
    }

    public function dehydrate($context)
    {
        // Only add islands as an effect if it's a subsequent request.
        if (! $context->component instanceof \Livewire\Component) return;
        if (! method_exists($context->component, 'getIslands')) return;
        if (! request()->hasHeader('X-Livewire')) return;

        if (! $islands = $context->component->getIslands()) return;

        $islands = collect($islands)->map(fn($island) => $island->toJson())->toArray();

        $context->addEffect('islands', $islands);
    }
}