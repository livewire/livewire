<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\ComponentHook;

use function Livewire\on;

class SupportIslands extends ComponentHook
{
    protected static $islands = [];

    static function provide()
    {
        Blade::precompiler(function ($content) {
            ray('currentPath', Blade::getPath());
            // ray('precompiler', $content);
            return static::compileIslands($content);
        });

        Blade::directive('island', function ($expression) {
            // ray('islandRender', $expression);
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

        $islands = collect($memo['islands'])->map(fn ($island) => $this->component->island($island['name'], mode: $island['mode']))->toArray();

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

    static function compileIslands($content)
    {
        // ray('before content', $content);

        $viewDirectory = storage_path('framework/views/livewire/views');

        File::ensureDirectoryExists($viewDirectory);

        do {
            $pattern = '/@island(?:\((.*?)\))?|@endisland/s';
            preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

            // ray('matches', $matches);

            $found = false;
            $startIslandPosition = null;
            $startIslandLength = null;
            $islandName = null;
            $islandData = null;
            $endIslandPosition = null;
            $endIslandLength = null;

            foreach ($matches[0] as $i => $match) {
                $match = $matches[0][$i][0];
                $offset = $matches[0][$i][1];
                $params = $matches[1][$i][0];

                if (str_starts_with($match, '@island')) {
                    $name = null;
                    $data = $params;
                    if (!empty($data)) {
                        $dataArray = array_map('trim', preg_split('/,(?![^\[\]]*\])/', $data));
                        // ray('dataArray', $dataArray);

                        $viewIndex = null;

                        foreach ($dataArray as $j => $dataParam) {
                            if (str_starts_with($dataParam, 'view:')) {
                                $viewIndex = $j;
                                // ray('skipping compiled island', $dataParam);
                                continue;
                            }

                            if ($j === 0 && !preg_match('/^\w[\w\d_]*\s*:/', $dataParam)) {
                                $name = trim($dataParam, "\"'");
                                unset($dataArray[$j]);
                                continue;
                            }

                            if (preg_match('/^name:\s*(["\'])([^"\']+)\1$/', $dataParam, $nameMatch)) {
                                $name = $nameMatch[2];
                                unset($dataArray[$j]);
                            }
                        }

                        // If a view parameter is provided, we need to compile the island and replace the directive with a compiled island directive...
                        if ($viewIndex !== null) {
                            $viewName = trim(substr($dataArray[$viewIndex], 5));

                            if (str_contains($viewName, 'livewire-compiled::island_')) {
                                // ray('skipping compiled island', $viewName);
                                continue;
                            }

                            unset($dataArray[$viewIndex]);

                            if (is_null($name)) {
                                $name = uniqid();
                            }

                            $islandContent = "@include({$viewName})";

                            $compiledViewName = "livewire-compiled::island_{$name}";
                            $compiledFileName = "island_{$name}.blade.php";
                            $compiledPath = $viewDirectory . DIRECTORY_SEPARATOR . $compiledFileName;
                            File::put($compiledPath, $islandContent);

                            // ray('DATA ARRAY', $dataArray);

                            $data = implode(', ', $dataArray);

                            $newContent = "@island('{$name}'" . ($data ? ", {$data}" : "") . ", view: '{$compiledViewName}')";

                            // ray('replace view island', $newContent, $viewName);

                            $content = substr_replace($content, $newContent, $offset, strlen($match));

                            // ray('content replaced', $content);

                            $found = true;
                            continue 2;
                        }
                    }

                    if (is_null($name)) {
                        $name = uniqid();
                    }

                    // ray('startIslandFound', $name, $data, gettype($data));
                    $startIslandPosition = $offset;
                    $startIslandLength = strlen($match);
                    $islandName = $name;
                    $islandData = $data;
                } else if (str_starts_with($match, '@endisland')) {
                    $endIslandPosition = $offset + strlen($match);
                    $endIslandLength = strlen($match);
                }

                if ($startIslandPosition && $endIslandPosition) {
                    // ray('startIslandPosition', $startIslandPosition, 'endIslandPosition', $endIslandPosition);

                    $islandContent = substr($content, $startIslandPosition, $endIslandPosition - $startIslandPosition);
                    // ray('islandContent', $islandContent);

                    $newContent = "@island('{$islandName}'" . ($islandData ? ", {$islandData}" : "") . ", view: 'livewire-compiled::island_{$islandName}')";

                    $content = substr_replace($content, $newContent, $startIslandPosition, $endIslandPosition - $startIslandPosition);

                    $islandContent = substr($islandContent, $startIslandLength, -$endIslandLength);

                    // ray('island content replaced', $newContent, $islandContent);

                    $compiledViewName = 'livewire-compiled::island_' . $islandName;
                    $compiledFileName = 'island_' . $islandName . '.blade.php';
                    $compiledPath = $viewDirectory . DIRECTORY_SEPARATOR . $compiledFileName;
                    File::put($compiledPath, $islandContent);

                    $found = true;
                    break;
                }
            }

            if (is_null($startIslandPosition) && !is_null($endIslandPosition)) {
                throw new \Exception('There is a `@endisland` directive that does not have a corresponding `@island` directive.');
            }

            if (!is_null($startIslandPosition) && is_null($endIslandPosition)) {
                throw new \Exception('There is a `@island` directive that does not have a corresponding `@endisland` directive.');
            }
        } while ($found);

        // rd('final content', $content);

        return $content;
    }
}
