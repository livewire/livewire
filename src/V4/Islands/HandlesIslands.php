<?php

namespace Livewire\V4\Islands;

use Livewire\Drawer\Utils;

trait HandlesIslands
{
    protected $islands = [];

    // @todo: hack...
    public $isSubsequentRequest = false;

    public function renderIsland($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        return $this->island($name, $view, $data, $mode, $defer, $fromBladeDirective);
    }

    public function island($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        // If second parameter is an array, treat it as data and use lookup for view
        if (is_array($view)) {
            $data = $view;
            $view = null;
        }

        // If no view provided, try to look it up from compiled islands
        if ($view === null && property_exists($this, 'islandLookup') && isset($this->islandLookup[$name])) {
            $view = $this->islandLookup[$name];
        }

        // If still no view, throw helpful error
        if ($view === null) {
            throw new \InvalidArgumentException(
                "No view specified for island '{$name}'. " .
                "Either provide a view name or define the island inline in your component."
            );
        }

        if ($mode === null) {
            $mode = ($this->isSubsequentRequest && $fromBladeDirective) ? 'skip' : 'replace';
        }

        if ($fromBladeDirective) {
            if ($this->isSubsequentRequest) {
                if ($mode === 'once') {
                    return new SkippedIsland($name);
                }
            } else {
                if ($mode === 'once') {
                    // ...
                } if ($mode === 'skip') {
                    return new SkippedIsland($name);
                } elseif ($defer || $mode === 'defer') {
                    return new DeferredIsland($name);
                } elseif ($mode === 'lazy') {
                    return new LazyIsland($name);
                }
            }
        }

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this);

        $island = new Island($name, $view, array_merge($componentData, $data), $this, $mode);

        if (! $fromBladeDirective) {
            $this->skipRender();

            $this->islands[] = $island;
        }

        return $island;
    }

    // This method is called from the frontend via: wire:click="$island('name...')"
    // This method has been whitelisted in HandleComponents.php
    public function __island($name)
    {
        $this->island($name);
    }

    public function getIslands(): array
    {
        return $this->islands;
    }

    public function popLastIsland()
    {
        return array_pop($this->islands);
    }

    public function getNamelessIslandName(): ?string
    {
        if (! property_exists($this, 'islandLookup')) return null;

        $name = array_key_first($this->islandLookup);

        if (! str_starts_with($name, 'island_')) {
            return null;
        }

        return $name;
    }
}
