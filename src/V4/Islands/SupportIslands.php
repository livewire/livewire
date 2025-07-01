<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\ComponentHook;

class SupportIslands extends ComponentHook
{
    static function provide()
    {
        Blade::precompiler(function ($content) {
            // ray('precompiler', $content);
            return static::compileIslands($content);
        });

        Blade::directive('island', function ($expression) {
            return "<?php if (isset(\$__livewire)) echo \$__livewire->island({$expression}); ?>";
        });

        // Blade::directive('island', function ($expression) {
        //     // If expression doesn't start with a named parameter, prepend a random name
        //     if (static::expressionStartsWithNamedParameter($expression)) {
        //         $randomName = "'" . uniqid('island_') . "'";
        //         $expression = $randomName . ($expression ? ', ' . $expression : '');
        //     }

        //     return "<?php if (isset(\$__livewire)) echo \$__livewire->island({$expression}, fromBladeDirective: true); ?".">";
        // });
    }

    static function expressionStartsWithNamedParameter($expression)
    {
        // Check if expression starts with a named parameter (word followed by colon)
        return !! preg_match('/^\s*\w+\s*:/', trim($expression));
    }

    function hydrate($memo)
    {
        $this->component->isSubsequentRequest = true;

        // ray('hydrate', $memo);

        if (! isset($memo['islands'])) return;

        // ray($memo['islands']);

        $islands = collect($memo['islands'])->map(fn($island) => $this->component->island($island['name'], mode: $island['mode']))->toArray();

        $this->component->setIslands($islands);
    }

    function call($method, $params, $returnEarly)
    {
        // if context contains islands, then we should loop through and render them...
    }

    function dehydrate($context)
    {
        ray('dehydrate', $this->component->getIslands());

        if (! $islands = $this->component->getIslands()) return;

        ray('dehydrate', $islands);

        $islandsObjects = collect($islands)->map(fn($island) => $island->toJson())->toArray();

        $context->addMemo('islands', $islandsObjects);

        // Only add islands as an effect if it's a subsequent request.
        // Otherwise, they are rendered in-place in the view...
        if ($context->isMounting()) return;

        ray('here');

        $islandRenders = collect($islands)
            ->filter(fn($island) => in_array($island->name, $this->component->islandsToRender))
            ->ray()
            ->map(fn($island) => [
                'name' => $island->name,
                'content' => $island->toHtml(),
            ])
            ->values()
            ->toArray();

        $context->addEffect('islands', $islandRenders);
    }

    static function compileIslands($content)
    {
        $viewsDirectory = storage_path('framework/views/livewire/views');

        // Ensure the views directory exists
        File::ensureDirectoryExists($viewsDirectory);

        $pattern = '/@island\s*(?:\((.*?)\))?(.*?)@endisland/s';

        $content = preg_replace_callback($pattern, function ($matches) use ($viewsDirectory) {
            $parameters = isset($matches[1]) ? trim($matches[1]) : '';
            $islandContent = trim($matches[2]);

            if (!empty($parameters)) {
                if (preg_match('/^[\'"]([^\'"]+)[\'"](?:\s*,\s*(.*))?$/', $parameters, $paramMatches)) {
                    $islandName = $paramMatches[1];
                    $islandData = isset($paramMatches[2]) && !empty(trim($paramMatches[2])) ? trim($paramMatches[2]) : '[]';
                } else {
                    $islandName = uniqid();
                    $islandData = $parameters;
                }
            } else {
                // Bare @island with no parameters at all
                $islandName = uniqid();
                $islandData = '[]';
            }

            // Remove any trailing commas if there are any...
            $islandData = rtrim($islandData, ',');

            $islandViewName = 'livewire-compiled::island_' . $islandName;
            $islandFileName = 'island_' . $islandName . '.blade.php';

            $islandPath = $viewsDirectory . '/' . $islandFileName;

            // ray('island', $islandName, $islandViewName, $islandFileName, $islandPath);

            File::put($islandPath, $islandContent);

            return "@island('{$islandName}', {$islandData}, view: '{$islandViewName}')";
        }, $content);

        // ray('islandCompiled', $content);

        return $content;
    }
}
