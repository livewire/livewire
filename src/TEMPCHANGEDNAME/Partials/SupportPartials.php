<?php

namespace Livewire\V4\Partials;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportPartials extends ComponentHook
{
    static function provide()
    {
        Blade::directive('partial', function ($expression) {
            return "<?php if (isset(\$_instance)) echo \$_instance->partial({$expression}); ?>";
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