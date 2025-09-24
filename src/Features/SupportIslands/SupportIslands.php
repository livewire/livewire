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
            return "<?php if (isset(\$__livewire)) echo \$__livewire->renderIslandExpression({$expression}); ?>";
        });
    }

    public static function registerInlineIslandPrecompiler()
    {
        Blade::precompiler(function ($content) {
            // Shortcut out if there are no islands in the content...
            if (! str_contains($content, '@endisland')) return $content;

            $pathSignature = Blade::getPath() ?: crc32($content);

            return IslandCompiler::compile($pathSignature, $content);
        });
    }

    function call($method, $params, $returnEarly, $context, $componentContext)
    {
        if (! isset($context['island'])) return;

        $island = $context['island'];

        // if context contains an island, then we should render it...
        return function (...$params) use ($island, $componentContext) {
            ['name' => $name, 'mode' => $mode] = $island;

            $islands = $this->component->getIslands();

            $token = $islands[$name] ?? null;

            if (! $token) return;

            $this->component->skipRender();

            $html = $this->component->renderIslandExpression(token: $token);

            $componentContext->pushEffect('islands', [
                'key' => $name,
                'content' => $html,
                'mode' => $mode,
            ]);
        };
    }

    public function dehydrate($context)
    {
        $context->addMemo('islands', $this->component->getIslands());
    }

    public function hydrate($memo)
    {
        $islands = $memo['islands'] ?? null;

        if (! $islands) return;

        $this->component->setIslands($islands ?? []);
    }
}
