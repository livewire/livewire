<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

use function Livewire\on;

class SupportIslands extends ComponentHook
{
    protected static $islands = [];

    static function provide()
    {
        Blade::precompiler(function ($content) {
            $path = Blade::getPath();

            return (new IslandsCompiler)->compile($content, $path);
        });

        Blade::directive('island', function ($expression) {
            return "<?php if (isset(\$__livewire)) echo \$__livewire->island({$expression}); ?>";
        });

        on('flush-state', function () {
            static::$islands = [];
        });
    }

    static function expressionStartsWithNamedParameter($expression)
    {
        // Check if expression starts with a named parameter (word followed by colon)
        return !! preg_match('/^\s*\w+\s*:/', trim($expression));
    }

    function context($context)
    {
        if (! isset($context['islands'])) return;

        static::$islands[$this->component->getId()] = $context['islands'];
    }

    function hydrate($memo)
    {
        $this->component->isSubsequentRequest = true;

        if (! isset($memo['islands'])) return;

        $islands = collect($memo['islands'])->map(fn ($island) => $this->component->island(name: $island['name'], key: $island['key'], data: ['__livewire' => $this->component], mode: $island['mode']))->toArray();

        $this->component->setIslands($islands);
    }

    // @todo: decide where it would be better to have a hook that runs once after all the calls have happened but before rendering of the component starts...
    function call($method, $params, $returnEarly, $context)
    {
        // if context contains islands, then we should loop through and render them...
        return function (...$params) use ($context) {
            if (! isset(static::$islands[$this->component->getId()])) return;

            if (! $islands = $this->component->getIslands()) return;

            $this->component->skipRender();

            $islandsToRender = static::$islands[$this->component->getId()];

            $islandRenders = collect($islands)
                ->filter(fn($island) => in_array($island->name, $islandsToRender))
                ->map(fn($island) => [
                    'name' => $island->name,
                    'key' => $island->key,
                    'content' => $island->toHtml(),
                ])
                ->values()
                ->toArray();

            $context->addEffect('islands', $islandRenders);

            // This ensures that if there are multiple calls, that this is only called once per request...
            unset(static::$islands[$this->component->getId()]);
        };
    }

    function dehydrate($context)
    {
        if (! $islands = $this->component->getIslands()) return;

        $islandsObjects = collect($islands)->map(fn($island) => $island->toJson())->toArray();

        $context->addMemo('islands', $islandsObjects);
    }
}
