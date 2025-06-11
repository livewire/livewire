<?php

namespace Livewire\V4\Partials;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportPartials extends ComponentHook
{
    static function provide()
    {
        Blade::directive('partial', function ($expression) {
            // If expression doesn't start with a named parameter, prepend a random name
            if (static::expressionStartsWithNamedParameter($expression)) {
                $randomName = "'" . uniqid('partial_') . "'";
                $expression = $randomName . ($expression ? ', ' . $expression : '');
            }

            return "<?php if (isset(\$_instance)) echo \$_instance->partial({$expression}, fromBladeDirective: true); ?>";
        });
    }

    static function expressionStartsWithNamedParameter($expression)
    {
        // Check if expression starts with a named parameter (word followed by colon)
        return !! preg_match('/^\s*\w+\s*:/', trim($expression));
    }

    function hydrate()
    {
        $this->component->isSubsequentRequest = true;
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