<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Features\SupportStreaming\SupportStreaming;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Drawer\Utils;

use function Livewire\trigger;

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

    public function renderIslandDirective($name = null, $token = null, $lazy = false, $defer = false, $always = false, $skip = false, $with = [])
    {
        // If no name is provided, use the token...
        $name = $name ?? $token;

        if ($this->islandIsMounting()) {
            $this->storeIsland($name, $token);

            if ($skip) {
                // Just render the placeholder...
                $renderedContent = $this->renderIslandView($name, $token, [
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

        if (($lazy || $defer) && $this->islandIsMounting()) {
            $renderedContent = $this->renderIslandView($name, $token, [
                '__placeholder' => '',
            ]);

            $directive = $lazy ? 'wire:intersect.once' : 'wire:init';

            $renderedContent = $this->injectLazyDirective($renderedContent, $name, $directive);
        } else {
            // Don't pass directive's $with - it's extracted in the compiled island
            $renderedContent = $this->renderIslandView($name, $token, []);
        }

        return $this->wrapWithFragmentMarkers($renderedContent, [
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

    public function renderIsland($name, $content = null, $mode = 'morph', $with = [], $mount = false)
    {
        $islands = $this->getIslands();

        foreach ($islands as $island) {
            if ($island['name'] === $name) {

                // If the island is lazy, we need to mount it, but to ensure any nested islands render,
                // we need to set the `$islandsHaveMounted` flag to false and reset it back after the
                // lazy island is mounted...
                $finish = $this->mountIfNeedsMounting($mount);

                $token = $island['token'];

                if (! $token) continue;

                // Pass runtime $with as __runtimeWith so it overrides directive's with
                $data = empty($with) ? [] : ['__runtimeWith' => $with];

                $renderedContent = $this->wrapWithFragmentMarkers($content ?? $this->renderIslandView($name, $token, $data), [
                    'type' => 'island',
                    'name' => $name,
                    'token' => $token,
                    'mode' => $mode,
                ]);

                $this->renderedIslandFragments[] = $renderedContent;

                $finish();
            }
        }
    }

    public function streamIsland($name, $content = null, $mode = 'morph', $with = [])
    {
        $islands = $this->getIslands();

        foreach ($islands as $island) {
            if ($island['name'] === $name) {
                $token = $island['token'];

                // Pass runtime $with as __runtimeWith so it overrides directive's with
                $data = empty($with) ? [] : ['__runtimeWith' => $with];

                $output = $content ?? $this->renderIslandView($name, $token, $data);

                $renderedContent = $this->wrapWithFragmentMarkers($output, [
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
        }
    }

    public function renderIslandView($name, $token, $data = [])
    {
        $path = IslandCompiler::getCachedPathFromToken($token);

        $view = app('view')->file($path);

        app(ExtendBlade::class)->startLivewireRendering($this);

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($this);

        $scope = array_merge(['__livewire' => $this], $properties);

        $view->with($scope);

        $view->with($data);

        $finish = trigger('renderIsland', $this, $name, $view, $properties);

        $html = $view->render();

        $replaceHtml = function ($newHtml) use (&$html) {
            $html = $newHtml;
        };

        $finish($html, $replaceHtml);

        app(ExtendBlade::class)->endLivewireRendering();

        return $html;
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

    protected function injectLazyDirective($content, $islandName, $directive)
    {
        $attributes = $directive.'="__lazyLoadIsland"';

        // Fast regex to find first HTML element opening tag (not comments or closing tags)
        // Matches: <tagname followed by whitespace, >, or />
        if (preg_match('/<([a-zA-Z][a-zA-Z0-9-]*)(\s|>|\/>)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $fullMatch = $matches[0][0];
            $position = $matches[0][1];
            $tagName = $matches[1][0];
            $afterTag = $matches[2][0];

            // Insert attributes after the tag name
            $insertion = '<'.$tagName.' '.$attributes.$afterTag;

            return substr($content, 0, $position) . $insertion . substr($content, $position + strlen($fullMatch));
        }

        // No element found (text-only content), wrap it
        return '<span '.$attributes.'>'.$content.'</span>';
    }

    protected function mountIfNeedsMounting($mount)
    {
        if (! $mount) {
            return function() {};
        }

        $existingMounted = $this->islandsHaveMounted;

        $this->islandsHaveMounted = false;

        return function() use ($existingMounted) {
            $this->islandsHaveMounted = $existingMounted;
        };
    }
}
