<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Drawer\Utils;

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
            $this->renderIslandViewWithScope(
                $viewInstance
            ),
            $name,
        );
    }

    public function renderIslandViewWithScope($view)
    {
        app(ExtendBlade::class)->startLivewireRendering($this);

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this);

        // We need to ensure that the component instance is available in the island view, so any nested islands can access it...
        $output = $view->with(array_merge($componentData, ['__livewire' => $this]))->render();

        app(ExtendBlade::class)->endLivewireRendering();

        return $output;
    }

    protected function decorateIslandWithMarker($output, $name)
    {
        return "<!--[if ISLAND:{$name}]><![endif]-->"
            . $output
            . "<!--[if ENDISLAND:{$name}]><![endif]-->";
    }
}
