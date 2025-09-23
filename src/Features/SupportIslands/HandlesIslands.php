<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Features\SupportIslands\Compiler\IslandCompiler;

trait HandlesIslands
{
    protected $islands = [];

    public function getIslands()
    {
        return $this->islands;
    }

    public function setIslands($islands)
    {
        $this->islands = $islands;
    }

    public function renderIslandExpression($name = null, $token = null)
    {
        $path = IslandCompiler::getCachedPathFromToken($token);

        $viewInstance = app('view')->file($path);

        // If no name is provided, use the token...
        $name = $name ?? $token;

        $this->islands[$name] = $token;

        return $this->decorateIslandWithMarker(
            $viewInstance->render(), $name,
        );
    }

    protected function decorateIslandWithMarker($output, $name)
    {
        return "<!--[if ISLAND:{$name}]><![endif]-->"
            . $output
            . "<!--[if ENDISLAND:{$name}]><![endif]-->";
    }
}
