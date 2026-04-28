<?php

namespace Livewire\Compiler;

use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;

class Compiler
{
    protected $prepareViewsForCompilationUsing = [];

    public function __construct(
        public CacheManager $cacheManager,
    ) {}

    public function compile(string $path): string
    {
        if (! file_exists($path)) {
            throw new \Exception("File not found: [{$path}]");
        }

        if (
            (! $this->cacheManager->hasBeenCompiled($path))
            || $this->cacheManager->isExpired($path)
        ) {
            $this->compilePath($path);
        }

        return $this->cacheManager->getClassName($path);
    }

    public function compilePath(string $path): void
    {
        $parser = is_file($path)
            ? SingleFileParser::parse($this, $path)
            : MultiFileParser::parse($this, $path);

        $viewFileName = $this->cacheManager->getViewPath($path);

        $placeholderFileName = null;
        $scriptFileName = null;
        $styleFileName = null;
        $globalStyleFileName = null;

        $placeholderContents = $parser->generatePlaceholderContents();
        $scriptContents = $parser->generateScriptContents();
        $styleContents = $parser->generateStyleContents();
        $globalStyleContents = $parser->generateGlobalStyleContents();

        if ($placeholderContents !== null) {
            $placeholderFileName = $this->cacheManager->getPlaceholderPath($path);

            $this->cacheManager->writePlaceholderFile($path, $placeholderContents);
        }

        if ($scriptContents !== null) {
            $scriptFileName = $this->cacheManager->getScriptPath($path);

            $this->cacheManager->writeScriptFile($path, $scriptContents);
        }

        if ($styleContents !== null) {
            $styleFileName = $this->cacheManager->getStylePath($path);

            $this->cacheManager->writeStyleFile($path, $styleContents);
        }

        if ($globalStyleContents !== null) {
            $globalStyleFileName = $this->cacheManager->getGlobalStylePath($path);

            $this->cacheManager->writeGlobalStyleFile($path, $globalStyleContents);
        }

        $this->cacheManager->writeViewFile($path, $parser->generateViewContents());

        // The class file is written last so its presence on disk implies every
        // file it references (view, placeholder, script, styles) is already
        // there. `CacheManager::hasBeenCompiled()` checks only the class file,
        // so a concurrent reader that observes the class is guaranteed to see
        // a complete cache rather than a half-written one.
        $this->cacheManager->writeClassFile($path, $parser->generateClassContents(
            $viewFileName,
            $placeholderFileName,
            $scriptFileName,
            $styleFileName,
            $globalStyleFileName,
        ));
    }

    public function clearCompiled($output = null)
    {
        $this->cacheManager->clearCompiledFiles($output);
    }

    public function prepareViewsForCompilationUsing($callback)
    {
        $this->prepareViewsForCompilationUsing[] = $callback;
    }

    public function prepareViewForCompilation($contents, $path)
    {
        foreach ($this->prepareViewsForCompilationUsing as $callback) {
            $contents = $callback($contents, $path);
        }

        return $contents;
    }
}
