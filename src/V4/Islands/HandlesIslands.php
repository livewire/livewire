<?php

namespace Livewire\V4\Islands;

trait HandlesIslands
{
    protected $islands = [];

    public function island($name, $key, $data = [], $mode = null, $defer = false, $lazy = false, $view = null, $placeholder = null)
    {
        if ($view === null && $key) {
            // @todo: See if there is a better way to do this...
            $view = "livewire-compiled::{$key}";
        }

        // If no view, throw helpful error
        if ($view === null) {
            throw new \InvalidArgumentException(
                "No view specified for island '{$name}'. " .
                "Either provide a view name or define the island inline in your component."
            );
        }

        if ($defer && $lazy) {
            throw new \InvalidArgumentException(
                "Cannot use both 'defer' and 'lazy' for island '{$name}'."
            );
        }

        if ($island = collect($this->islands)->first(fn($island) => $island->name === $name && $island->key === $key)) {
            return $island;
        }

        if ($mode === null) {
            $mode = 'replace';
        }

        if ($mode === 'skip') {
            $island = new SkippedIsland($name, $key);
        } elseif ($lazy) {
            $island = new LazyIsland($name, $key, $mode, $placeholder);
        } elseif ($defer) {
            $island = new DeferredIsland($name, $key, $mode, $placeholder);
        } else {
            $island = new Island($name, $key, $view, $data, $this, $mode);
        }

        $this->islands[] = $island;

        return $island;
    }

    public function setIslands($islands): void
    {
        $this->islands = $islands;
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
