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

    function context($context)
    {
        if (! isset($context['islands'])) return;

        static::$islands[$this->component->getId()] = $context['islands'];
    }

    function hydrate($memo)
    {
        $this->component->isSubsequentRequest = true;

        if (! isset($memo['islands'])) return;

        $islands = collect($memo['islands'])->map(fn ($island) => $this->component->island(name: $island['name'], key: $island['key'], mode: $island['mode'], render: $island['render']))->toArray();

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

            $islandsToRender = collect(static::$islands[$this->component->getId()])->keyBy('name');

            $islandRenders = collect($islands)
                ->filter(fn($island) => $islandsToRender->has($island->name))
                ->map(fn($island) => [
                    'name' => $island->name,
                    'key' => $island->key,
                    'mode' => $islandsToRender->get($island->name)['mode'] ?? null,
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
