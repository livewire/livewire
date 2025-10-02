<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Drawer\Utils;

trait HandlesIslands
{
    protected $islands = [];
    protected $islandsHaveMounted = false;
    protected $islandIsTopLevelRender = false;

    public function islandIsMounting()
    {
        return ! $this->islandsHaveMounted;
    }

    public function markIslandsAsMounted()
    {
        $this->islandsHaveMounted = true;
    }

    public function getIslands()
    {
        return $this->islands;
    }

    public function setIslands($islands)
    {
        $this->islands = $islands;
    }

    public function renderIslandDirective($name = null, $token = null, $lazy = false, $defer = false, $always = false)
    {
        // If no name is provided, use the token...
        $name = $name ?? $token;

        if ($this->islandIsMounting()) {
            $this->storeIsland($name, $token);
        } else {
            if (! $always) {
                return $this->renderSkippedIsland($name, $token);
            }
        }

        if ($lazy) {
            $renderedContent = $this->renderIslandView($token, [
                '__placeholder' => '',
            ]);

            $renderedContent = '<div wire:intersect="$refresh">' . $renderedContent . '</div>';
        } elseif ($defer) {
            $renderedContent = $this->renderIslandView($token, [
                '__placeholder' => '',
            ]);

            $renderedContent = '<div wire:init="$refresh">' . $renderedContent . '</div>';
        } else {
            $renderedContent = $this->renderIslandView($token);
        }

        return $this->wrapWithFragmentMarkers($renderedContent,[
            'type' => 'island',
            'name' => $name,
            'token' => $token,
            'mode' => 'morph',
        ]);
    }

    public function renderSkippedIsland($name, $token)
    {
        return $this->wrapWithFragmentMarkers('', [
            'type' => 'island',
            'name' => $name,
            'token' => $token,
            'mode' => 'skip',
        ]);
    }

    public function renderIsland($name, $token, $mode)
    {
        return $this->wrapWithFragmentMarkers($this->renderIslandView($token), [
            'type' => 'island',
            'name' => $name,
            'token' => $token,
            'mode' => $mode,
        ]);
    }

    public function renderIslandView($token, $data = [])
    {
        $path = IslandCompiler::getCachedPathFromToken($token);

        $view = app('view')->file($path);

        app(ExtendBlade::class)->startLivewireRendering($this);

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this);

        $scope = array_merge(['__livewire' => $this], $componentData, $data);

        $output = $view->with($scope)->render();

        app(ExtendBlade::class)->endLivewireRendering();

        return $output;
    }

    protected function wrapWithFragmentMarkers($output, $metadata)
    {
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
        $this->islands[] = [
            'name' => $name,
            'token' => $token,
        ];
    }
}
