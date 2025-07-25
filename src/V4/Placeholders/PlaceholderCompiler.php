<?php

namespace Livewire\V4\Placeholders;

use Illuminate\Support\Facades\File;
use Livewire\ComponentHook;

class PlaceholderCompiler extends ComponentHook
{
    protected string $cacheDirectory;
    protected string $viewDirectory;
    protected string $viewName;
    protected array $placeholderStack = [];
    protected ?int $startPlaceholderCount = 0;

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
        // Strip ⚡ from the view name to ensure clean component names
        $this->viewName = str_replace('⚡', '', $this->viewName);

        $content = $this->compilePlaceholders($content);

        return $content;
    }

    function compilePlaceholders($content)
    {
        $remainingContent = $content;
        $content = '';

        while ($directive = $this->findDirective($remainingContent)) {
            $content .= $directive['precedingContent'];
            $directiveContent = $directive['directiveContent'];
            $params = $directive['params'];
            $remainingContent = $directive['remainingContent'];

            if ($this->isPlaceholder($directiveContent)) {
                if ($this->startPlaceholderCount > 0) {
                    throw new \Exception('There should only be one @placeholder directive per view');
                }

                $this->startPlaceholderCount++;

                $placeholder = $this->getPlaceholderDetails($params);

                $this->placeholderStack[] = $placeholder;

                $remainingContent = $this->compilePlaceholders($remainingContent);

                $placeholder = array_pop($this->placeholderStack);

                $this->writePlaceholder($placeholder);
            } else if ($this->isEndPlaceholder($directiveContent)) {
                if (empty($this->placeholderStack)) {
                    throw new \Exception('End placeholder directive found without a matching start placeholder directive');
                }

                $this->placeholderStack[count($this->placeholderStack) - 1]['content'] = $content;

                return $remainingContent;
            }
        }

        if (! empty($this->placeholderStack)) {
            throw new \Exception('Start placeholder directive found without a matching end placeholder directive');
        }

        $content .= $remainingContent;

        return $content;
    }

    function findDirective($content)
    {
        $pattern = '/@placeholder(?:\((.*?)\))?|@endplaceholder/s';
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

    function isPlaceholder($match)
    {
        return str_starts_with($match, '@placeholder');
    }

    function isEndPlaceholder($match)
    {
        return str_starts_with($match, '@endplaceholder');
    }

    function getPlaceholderDetails($params)
    {
        $compiledViewKey = $this->viewName . '_placeholder';
        $compiledViewName = "livewire-compiled::{$compiledViewKey}";
        $compiledFileName = "{$compiledViewKey}.blade.php";

        $compiledPath = $this->viewDirectory . DIRECTORY_SEPARATOR . $compiledFileName;

        return [
            'compiledViewKey' => $compiledViewKey,
            'compiledViewName' => $compiledViewName,
            'compiledFileName' => $compiledFileName,
            'compiledPath' => $compiledPath,
            'content' => null,
        ];
    }

    function writePlaceholder($placeholder)
    {
        $content = $placeholder['content'];

        File::put($placeholder['compiledPath'], $content);
    }
}
