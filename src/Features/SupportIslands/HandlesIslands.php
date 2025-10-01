<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Drawer\Utils;

trait HandlesIslands
{
    protected $islands = [];
    protected $islandsHaveMounted = false;

    public function markIslandsAsMounted()
    {
        $this->hasMountedIslands = true;
    }

    public function getIslands()
    {
        return $this->islands;
    }

    public function setIslands($islands)
    {
        $this->islands = $islands;
    }

    public function renderIslandDirective($name = null, $token = null)
    {
        // If no name is provided, use the token...
        $name = $name ?? $token;

        if ($this->islandsHaveMounted) {
            $this->renderSkippedIsland($name);
        } else {
            $this->storeIsland($name, $token);
        }

        $path = IslandCompiler::getCachedPathFromToken($token);

        $viewInstance = app('view')->file($path);

        return $this->decorateIslandWithMarker(
            $this->renderIslandViewWithScope(
                $viewInstance
            ),
            $name,
        );
    }

    public function renderSkippedIsland($name)
    {
        return $this->decorateIslandWithMarker(
            '',
            $name,
            'skip',
        );
    }

    public function renderIsland($name, $token, $mode)
    {
        $path = IslandCompiler::getCachedPathFromToken($token);

        $viewInstance = app('view')->file($path);

        return $this->decorateIslandWithMarker(
            $this->renderIslandViewWithScope(
                $viewInstance
            ),
            $name,
            $mode,
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

    protected function decorateIslandWithMarker($output, $name, $mode = null)
    {
        $metadata = $mode ? [
            'type' => 'island',
            'name' => $name,
            'mode' => $mode,
        ] : [
            'type' => 'island',
            'name' => $name,
        ];

        $startFragment = "<!--[if FRAGMENT:{$this->encodeFragmentMetadata($metadata)}]><![endif]-->";

        $endFragment = "<!--[if ENDFRAGMENT:{$this->encodeFragmentMetadata($metadata)}]><![endif]-->";

        return $startFragment . $output . $endFragment;
    }

    protected function encodeFragmentMetadata($metadata)
    {
        $output = '';

        foreach ($metadata as $key => $value) {
            $output .= "{$key}={$value}|";
        }

        return rtrim($output, '|');
    }

    protected function storeIsland($name, $token)
    {
        $this->islands[$name] = $token;
    }
}
