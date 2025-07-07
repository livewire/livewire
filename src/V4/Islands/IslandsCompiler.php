<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\File;
use Livewire\ComponentHook;

class IslandsCompiler extends ComponentHook
{
    protected string $cacheDirectory;
    protected string $viewDirectory;
    protected string $viewName;
    protected array $islandsStack = [];
    protected ?int $startIslandCount = null;
    protected array $islandsNameCount = [];

    public function __construct(?string $cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory ?: storage_path('framework/views/livewire');
        $this->viewDirectory = $this->cacheDirectory . '/views';

        File::ensureDirectoryExists($this->viewDirectory);
    }

    function compile($content, $currentPath)
    {
        $extensions = [
            '.livewire.php',
            '.blade.php',
        ];

        $this->viewName = str_replace($extensions, '', basename($currentPath));

        $content = $this->compileIslands($content);

        return $content;
    }

    function compileIslands($content)
    {
        $remainingContent = $content;
        $content = '';

        while ($directive = $this->findDirective($remainingContent)) {
            $content .= $directive['precedingContent'];
            $directiveContent = $directive['directiveContent'];
            $params = $directive['params'];
            $remainingContent = $directive['remainingContent'];

            if ($this->isStartIsland($directiveContent)) {
                $this->startIslandCount = isset($this->startIslandCount) ? $this->startIslandCount + 1 : 0;

                if ($this->isCompiledIsland($params)) {
                    $content .= $directiveContent;

                    continue;
                }

                $island = $this->getIslandDetails($params);

                if (isset($island['view'])) {
                    $content .= $island['islandOutput'];

                    $this->writeIsland($island);

                    continue;
                }

                $this->islandsStack[] = $island;

                $remainingContent = $this->compileIslands($remainingContent);

                $island = array_pop($this->islandsStack);

                $content .= $island['islandOutput'];

                $this->writeIsland($island);
            } else if ($this->isEndIsland($directiveContent)) {
                if (empty($this->islandsStack)) {
                    throw new \Exception('End island directive found without a matching start island directive');
                }

                $this->islandsStack[count($this->islandsStack) - 1]['content'] = $content;

                return $remainingContent;
            }
        }

        if (! empty($this->islandsStack)) {
            throw new \Exception('Start island directive found without a matching end island directive');
        }

        $content .= $remainingContent;

        return $content;
    }

    function findDirective($content)
    {
        $pattern = '/@island(?:\((.*?)\))?|@endisland/s';
        preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if (! $matches) {
            return null;
        }

        $directiveContent = isset($matches[0][0]) ? $matches[0][0] : null;
        $directiveOffset = isset($matches[0][1]) ? $matches[0][1] : null;
        $params = isset($matches[1][0]) ? $matches[1][0] : null;

        $precedingContent = substr($content, 0, $directiveOffset);
        $remainingContent = substr($content, $directiveOffset + strlen($directiveContent));

        return [
            'precedingContent' => $precedingContent,
            'directiveContent' => $directiveContent,
            'params' => $params,
            'remainingContent' => $remainingContent,
        ];
    }

    function isStartIsland($match)
    {
        return str_starts_with($match, '@island');
    }

    function isCompiledIsland($match)
    {
        return str_contains($match, 'livewire-compiled::');
    }

    function isEndIsland($match)
    {
        return str_starts_with($match, '@endisland');
    }

    function getIslandDetails($params)
    {
        $parsedParams = $this->parseParams($params);

        $name = $parsedParams['name'];
        $view = $parsedParams['view'];
        $params = $parsedParams['params'];

        if (isset($name) && $name !== '') {
            $this->islandsNameCount[$name] = isset($this->islandsNameCount[$name]) ? $this->islandsNameCount[$name] + 1 : 0;

            $key = "{$name}_{$this->islandsNameCount[$name]}";
        } else {
            $name = "anonymous_{$this->startIslandCount}";
            $key = "{$this->startIslandCount}";

        }

        $compiledViewKey = "{$this->viewName}_island_{$key}";
        $compiledViewName = "livewire-compiled::{$compiledViewKey}";
        $compiledFileName = "{$compiledViewKey}.blade.php";

        $compiledPath = $this->viewDirectory . DIRECTORY_SEPARATOR . $compiledFileName;

        // @todo: Change this to use the key instead of compiled view key...
        $islandOutput = $this->compiledIslandDirective($name, $compiledViewKey, $params, $compiledViewName);

        $content = null;

        if (isset($view)) {
            $content = "@include('{$view}')";
        }

        return [
            'content' => $content,
            'name' => $name,
            'key' => $key,
            'view' => $view,
            'params' => $params,
            'compiledViewName' => $compiledViewName,
            'compiledFileName' => $compiledFileName,
            'compiledPath' => $compiledPath,
            'islandOutput' => $islandOutput,
        ];
    }

    function parseParams($params)
    {
        // Split the params into an array...
        $paramsArray = array_map('trim', preg_split('/,(?![^\[\]]*\])/', $params));

        $name = null;
        $view = null;

        foreach ($paramsArray as $index => $param) {
            // If the first param is not a named parameter, it's the name of the island...
            if ($index === 0 && !preg_match('/^\w[\w\d_]*\s*:/', $param)) {
                // If the param is wrapped in quotes, remove the quotes...
                $name = preg_match('/^([\'"])(.*?)\1$/', $param, $m) ? $m[2] : trim($param);
                unset($paramsArray[$index]);
                continue;
            }

            // If the param has a `name:` prefix, it's the name of the island...
            if (preg_match('/^name:\s*([\'"])(.*?)\1$/', $param, $m)) {
                $name = $m[2];
                unset($paramsArray[$index]);
                continue;
            }

            // If the param has a `view:` prefix, it's the view of the island...
            if (preg_match('/^view:\s*([\'"])(.*?)\1$/', $param, $m)) {
                $view = $m[2];
                unset($paramsArray[$index]);
                continue;
            }
        }

        return [
            'name' => $name,
            'view' => $view,
            'params' => $paramsArray,
        ];
    }

    function compiledIslandDirective($name, $key, $params, $compiledViewName)
    {
        $output = "@island('{$name}', key: '{$key}'";

        if ($params) {
            $output .= ", " . implode(', ', $params);
        }

        $output .= ", view: '{$compiledViewName}')";

        return $output;
    }

    function writeIsland($island)
    {
        $content = $island['content'];

        File::put($island['compiledPath'], $content);
    }
}
