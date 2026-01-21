<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportIslands extends ComponentHook
{
    public static function provide()
    {
        static::registerInlineIslandPrecompiler();
        static::registerIslandDirective();
    }

    public static function registerIslandDirective()
    {
        Blade::directive('island', function ($expression) {
            return "<?php if (isset(\$__livewire)) echo \$__livewire->renderIslandDirective({$expression}); ?>";
        });
    }

    public static function registerInlineIslandPrecompiler()
    {
        Blade::prepareStringsForCompilationUsing(function ($content) {
            // Shortcut out if there are no islands in the content...
            if (! str_contains($content, '@endisland')) return $content;

            $pathSignature = Blade::getPath() ?: crc32($content);

            return IslandCompiler::compile($pathSignature, $content);
        });
    }

    function call($method, $params, $returnEarly, $metadata, $componentContext)
    {
        if (! isset($metadata['island'])) return;

        $island = $metadata['island'];

        $mount = false;

        if ($method === '__lazyLoadIsland') {
            $mount = true;
            $returnEarly();
        }

        // if metadata contains an island, then we should render it...
        return function (...$params) use ($island, $componentContext, $mount, $metadata) {
            ['name' => $name, 'mode' => $mode] = $island;

            $islands = $this->component->getIslands();

            $islands = array_filter($islands, fn ($island) => $island['name'] === $name);

            if (empty($islands)) return;

            // Support `Wire:click.renderless`...
            if ($metadata['renderless'] ?? false) {
                return;
            }

            // If #[Renderless] attribute was used, don't render the island...
            if ($this->storeGet('skipIslandsRender', false)) return;

            $this->component->skipRender();

            $this->component->renderIsland(
                name: $name,
                mode: $mode,
                mount: $mount,
            );
        };
    }

    public function dehydrate($context)
    {
        $context->addMemo('islands', $this->component->getIslands());

        if ($this->component->hasRenderedIslandFragments()) {
            $context->addEffect('islandFragments', $this->component->getRenderedIslandFragments());
        }
    }

    public function hydrate($memo)
    {
        $this->component->markIslandsAsMounted();

        $islands = $memo['islands'] ?? null;

        if (! $islands) return;

        $this->component->setIslands($islands ?? []);
    }
}
