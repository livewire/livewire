<?php

namespace Livewire\V4\Islands;

use Livewire\Exceptions\MethodNotFoundException;

trait HandlesIslands
{
    protected $islands = [];

    // @todo: hack...
    public $isSubsequentRequest = false;

    // Alias for renderIsland
    public function renderIsland($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        return $this->island($name, $view, $data, $mode, $defer, $fromBladeDirective);
    }

    public function island($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        // Skip means we don't render the island at all (used for conditional rendering)
        if ($mode === 'skip') {
            return new SkippedIsland($name);
        }

        // Lazy loading for performance - island loads when it enters viewport
        if ($mode === 'lazy') {
            // If no view provided, try to look it up from compiled islands
            if ($view === null && property_exists($this, 'islandLookup') && isset($this->islandLookup[$name])) {
                $view = $this->islandLookup[$name];
            }

            if ($view === null && !$fromBladeDirective) {
                throw new MethodNotFoundException(
                    "No view specified for island '{$name}'. " .
                    "Either provide a view name or define the island inline in your component."
                );
            }

            return new LazyIsland($name);
        }

        // Get data for the view template
        $componentData = method_exists($this, 'getViewData') ? $this->getViewData() : [];

        // Defer means we don't render until explicitly requested
        if ($defer) {
            return new DeferredIsland($name);
        }

        if ($mode === 'defer') {
            return new DeferredIsland($name);
        }

        // If no view provided, try to look it up from compiled islands
        if ($view === null && property_exists($this, 'islandLookup') && isset($this->islandLookup[$name])) {
            $view = $this->islandLookup[$name];
        }

        if ($view === null && !$fromBladeDirective) {
            throw new MethodNotFoundException(
                "No view specified for island '{$name}'. " .
                "Either provide a view name or define the island inline in your component."
            );
        }

        $island = new Island($name, $view, array_merge($componentData, $data), $this, $mode);

        // Store for effects if this is not the initial render
        if (request()->hasHeader('X-Livewire')) {
            $this->islands[] = $island;
        }

        return $island;
    }

    // This method is called from the frontend via: wire:click="$island('name...')"
    // Notice the double underscore prefix - this is a magic method for frontend calls
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
}