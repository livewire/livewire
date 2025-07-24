<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportIslands extends ComponentHook
{
    static function provide()
    {
        Blade::precompiler(function ($content) {
            $path = Blade::getPath();

            return (new IslandsCompiler)->compile($content, $path);
        });

        Blade::directive('island', function ($expression) {
            return "<?php if (isset(\$__livewire)) echo \$__livewire->island({$expression}); ?>";
        });
    }

    function hydrate($memo)
    {
        $this->component->isSubsequentRequest = true;

        if (! isset($memo['islands'])) return;

        $islands = collect($memo['islands'])->map(fn ($island) => $this->component->island(name: $island['name'], key: $island['key'], mode: $island['mode'], render: $island['render'], poll: $island['poll']))->toArray();

        $this->component->setIslands($islands);
    }

    function call($method, $params, $returnEarly, $context, $componentContext)
    {
        if (! isset($context['island'])) return;

        // if context contains an island, then we should render it...
        return function (...$params) use ($context, $componentContext) {
            if (! $islands = $this->component->getIslands()) return;

            $islandToRender = [
                'name' => $context['island']['name'],
                // Mode is optional, so we need to check if it's set...
                'mode' => $context['island']['mode'] ?? null,
            ];

            $this->component->skipRender();

            $islandRenders = collect($islands)
                ->filter(fn($island) => $island->name === $islandToRender['name'])
                ->map(fn($island) => [
                    'name' => $island->name,
                    'key' => $island->key,
                    'mode' => $islandToRender['mode'],
                    'content' => $island->toHtml(),
                ])
                ->values()
                ->toArray();

            // @todo: Confirm if this works adding multiple islands effects...
            $componentContext->addEffect('islands', $islandRenders);
        };
    }

    function dehydrate($context)
    {
        if (! $islands = $this->component->getIslands()) return;

        $islandsObjects = collect($islands)->map(fn($island) => $island->toJson())->toArray();

        $context->addMemo('islands', $islandsObjects);
    }
}
