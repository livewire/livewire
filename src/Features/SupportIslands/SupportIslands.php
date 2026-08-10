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
        $compiler = app('blade.compiler');

        $compiler->prepareStringsForCompilationUsing(function ($content) use ($compiler) {
            // Shortcut out if there are no islands in the content...
            if (! str_contains($content, '@endisland')) return $content;

            $pathSignature = $compiler->getPath() ?: crc32($content);

            return IslandCompiler::compile($pathSignature, $content);
        });
    }

    public function dehydrate($context)
    {
        $context->addMemo('islands', $this->component->getIslands());

        $fragments = [
            ...$this->component->getRenderedIslandFragments(),
            ...($context->renderPlan?->islandFragments() ?? []),
        ];

        if (! empty($fragments)) {
            $context->addEffect('islandFragments', $fragments);
        }
    }

    public function hydrate($memo)
    {
        if (($memo['lazyLoaded'] ?? null) === false) return;

        $this->component->markIslandsAsMounted();

        $islands = $memo['islands'] ?? null;

        if (! $islands) return;

        $this->component->setIslands($islands ?? []);
    }
}
