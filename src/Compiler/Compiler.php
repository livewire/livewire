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

        $placeholderContents = $parser->generatePlaceholderContents();
        $scriptContents = $parser->generateScriptContents();

        if ($placeholderContents !== null) {
            $placeholderFileName = $this->cacheManager->getPlaceholderPath($path);

            $this->cacheManager->writePlaceholderFile($path, $placeholderContents);
        }

        if ($scriptContents !== null) {
            $scriptFileName = $this->cacheManager->getScriptPath($path);

            $this->cacheManager->writeScriptFile($path, $scriptContents);
        }

        $this->cacheManager->writeClassFile($path, $parser->generateClassContents(
            $viewFileName,
            $placeholderFileName,
            $scriptFileName,
        ));

        $this->cacheManager->writeViewFile($path, $parser->generateViewContents());
    }

    public function clearCompiled($output = null)
    {
        $this->cacheManager->clearCompiledFiles($output);
    }

    public function prepareViewsForCompilationUsing($callback)
    {
        $this->prepareViewsForCompilationUsing[] = $callback;
    }

    public function prepareViewForCompilation($contents)
    {
        foreach ($this->prepareViewsForCompilationUsing as $callback) {
            $contents = $callback($contents);
        }

        return $contents;
    }
}
