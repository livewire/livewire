<?php

namespace Livewire\Compiler;

use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;

class Compiler
{
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
            ? SingleFileParser::parse($path)
            : MultiFileParser::parse($path);

        $viewFileName = $this->cacheManager->getViewPath($path);
        $placeholderFileName = null;

        $placeholderContents = $parser->generatePlaceholderContents();

        if ($placeholderContents !== null) {
            $placeholderFileName = $this->cacheManager->getPlaceholderPath($path);

            $this->cacheManager->writePlaceholderFile($path, $placeholderContents);
        }

        $this->cacheManager->writeClassFile($path, $parser->generateClassContents($viewFileName, $placeholderFileName));

        $scriptContents = $parser->generateScriptContents();

        if ($scriptContents !== null) {
            $this->cacheManager->writeScriptFile($path, $scriptContents);
        }

        $this->cacheManager->writeViewFile($path, $parser->generateViewContents());
    }
}
