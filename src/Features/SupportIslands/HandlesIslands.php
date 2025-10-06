<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Features\SupportStreaming\SupportStreaming;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Drawer\Utils;

trait HandlesIslands
{
    protected $islands = [];
    protected $islandsHaveMounted = false;
    protected $islandIsTopLevelRender = false;
    protected $renderedIslandFragments = [];

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

    public function getRenderedIslandFragments()
    {
        return $this->renderedIslandFragments;
    }

    public function hasRenderedIslandFragments()
    {
        return ! empty($this->renderedIslandFragments);
    }

    public function renderIslandDirective($name = null, $token = null, $lazy = false, $defer = false, $always = false, $skip = false)
    {
        // If no name is provided, use the token...
        $name = $name ?? $token;

        if ($this->islandIsMounting()) {
            $this->storeIsland($name, $token);

            if ($skip) {
                // Just render the placeholder...
                $renderedContent = $this->renderIslandView($token, [
                    '__placeholder' => '',
                ]);

                return $this->wrapWithFragmentMarkers($renderedContent,[
                    'type' => 'island',
                    'name' => $name,
                    'token' => $token,
                    'mode' => 'morph',
                ]);
            }
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

    public function renderIsland($name, $content = null, $mode = 'morph')
    {
        $islands = $this->getIslands();

        foreach ($islands as $island) {
            if ($island['name'] === $name) {
                $token = $island['token'];

                if (! $token) continue;

                $renderedContent = $this->wrapWithFragmentMarkers($content ?? $this->renderIslandView($token), [
                    'type' => 'island',
                    'name' => $name,
                    'token' => $token,
                    'mode' => $mode,
                ]);

                $this->renderedIslandFragments[] = $renderedContent;
            }
        }
    }

    public function streamIsland($name, $content = null, $mode = 'morph')
    {
        $islands = $this->getIslands();

        foreach ($islands as $island) {
            if ($island['name'] === $name) {
                $token = $island['token'];
                break;
            }
        }

        if (! $token) return;

        $content = $content ?? $this->renderIslandView($token);

        $renderedContent = $this->wrapWithFragmentMarkers($content, [
            'type' => 'island',
            'name' => $name,
            'token' => $token,
            'mode' => $mode,
        ]);

        SupportStreaming::ensureStreamResponseStarted();

        SupportStreaming::streamContent([
            'id' => $this->getId(),
            'type' => 'island',
            'islandFragment' => $renderedContent,
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
