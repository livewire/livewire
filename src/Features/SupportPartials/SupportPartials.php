<?php

namespace Livewire\Features\SupportPartials;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportPartials extends ComponentHook
{
    static function provide()
    {
        Blade::directive('partial', function ($expression) {
            return "<?php if (isset(\$_instance)) echo \$_instance->partial({$expression}, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>";
        });
    }

    function dehydrate($context)
    {
        // Only add partials as an effect if it's a subsequent request.
        // Otherwise, they are rendered in-place in the view...
        if ($context->isMounting()) return;

        if (! $partials = $this->component->getPartials()) return;

        $partials = collect($partials)->map(fn($partial) => $partial->toJson())->toArray();

        $context->addEffect('partials', $partials);
    }
}