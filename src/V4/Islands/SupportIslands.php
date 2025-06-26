<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportIslands extends ComponentHook
{
    static function provide()
    {
        Blade::directive('placeholderisland', function ($expression) {
            // If expression doesn't start with a named parameter, prepend a random name
            if (static::expressionStartsWithNamedParameter($expression)) {
                $randomName = "'" . uniqid('island_') . "'";
                $expression = $randomName . ($expression ? ', ' . $expression : '');
            }

            return "<?php if (isset(\$_instance)) echo \$_instance->island({$expression}, fromBladeDirective: true); ?>";
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
        // Only add islands as an effect if it's a subsequent request.
        // Otherwise, they are rendered in-place in the view...
        if ($context->isMounting()) return;

        if (! $islands = $this->component->getIslands()) return;

        $islands = collect($islands)->map(fn($island) => $island->toJson())->toArray();

        $context->addEffect('islands', $islands);
    }
}
