<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\File;
use Livewire\ComponentHook;

class IslandsCompiler extends ComponentHook
{
    protected $viewDirectory = '';
    protected $islandsStack = [];
    protected $startIslandCount = null;
    protected $islandsNameCount = [];

    public function __construct()
    {
        $this->viewDirectory = storage_path('framework/views/livewire/views');

        File::ensureDirectoryExists($this->viewDirectory);
    }

    function compile($content, $currentPath)
    {
        $extensions = [
            '.livewire.php',
            '.blade.php',
        ];

        $viewName = str_replace($extensions, '', basename($currentPath));

        $content = $this->compileContent($content, $viewName);

        return $content;
    }

    function compileContent($content, $viewName)
    {
        // ray('compileContent', $content);
        $pattern = '/@island(?:\((.*?)\))?|@endisland/s';
        // preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        // ray('matches', $matches);

        $match = isset($matches[0][0]) ? $matches[0][0] : null;
        $matchOffset = isset($matches[0][1]) ? $matches[0][1] : null;
        $params = isset($matches[1][0]) ? $matches[1][0] : null;
        $paramsOffset = isset($matches[1][1]) ? $matches[1][1] : null;

        if (is_null($match)) {
            return $content;
        }

        // ray('match', $match, $matchOffset, $params);

        $beforeMatch = substr($content, 0, $matchOffset);
        $afterMatch = substr($content, $matchOffset + strlen($match));

        if ($this->isStartIsland($match)) {
            // ray('start island', $match, $this->islandsStack);
            $this->startIslandCount = isset($this->startIslandCount) ? $this->startIslandCount + 1 : 0;

            $parsedParams = $this->parseParams($params);

            $name = $parsedParams['name'];
            $view = $parsedParams['view'];
            $params = $parsedParams['params'];

            if (isset($name) && $name !== '') {
                $this->islandsNameCount[$name] = isset($this->islandsNameCount[$name]) ? $this->islandsNameCount[$name] + 1 : 0;

                $key = "{$name}_{$this->islandsNameCount[$name]}";

                $compiledViewName = "livewire-compiled::{$viewName}_island_{$key}";
                $compiledFileName = "{$viewName}_island_{$key}.blade.php";
            } else {
                $name = "anonymous_{$this->startIslandCount}";
                $key = "{$this->startIslandCount}";

                $compiledViewName = "livewire-compiled::{$viewName}_island_{$key}";
                $compiledFileName = "{$viewName}_island_{$key}.blade.php";
            }

            $compiledPath = $this->viewDirectory . DIRECTORY_SEPARATOR . $compiledFileName;

            $matchOriginal = $match;

            $match = $this->compiledIslandDirective($name, $params, $compiledViewName);

            if (count($this->islandsStack) > 0) {
                $this->islandsStack[count($this->islandsStack) - 1]['content'] .= $beforeMatch . $match;
            }

            $this->islandsStack[] = [
                'content' => '',
                'name' => $name,
                'compiledViewName' => $compiledViewName,
                'compiledFileName' => $compiledFileName,
                'compiledPath' => $compiledPath,
            ];

            if (isset($view)) {
                $this->islandsStack[count($this->islandsStack) - 1]['content'] = "@include({$view})";
                $this->writeIsland();
            }

            $afterMatch = $this->compileContent($afterMatch, $viewName);

        } else if ($this->isEndIsland($match)) {
            $this->islandsStack[count($this->islandsStack) - 1]['content'] .= $beforeMatch;

            $this->writeIsland();

            $afterMatch =  $this->compileContent($afterMatch, $viewName);

            return $afterMatch;
        } else {
            throw new \Exception('Something went wrong. Invalid island directive');
        }

        return $beforeMatch . $match . $afterMatch;
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

    function compiledIslandDirective($name, $params, $compiledViewName)
    {
        $output = "@island('{$name}'";

        if ($params) {
            $output .= ", " . implode(', ', $params);
        }

        $output .= ", view: '{$compiledViewName}')";

        return $output;
    }

    function writeIsland()
    {
        $island = array_pop($this->islandsStack);

        $content = $island['content'];

        ray('writing compiled island file', $island['compiledFileName'], $content);

        // File::put($island['compiledPath'], $content);
    }
}
