<?php

namespace Livewire\V4\Islands;

trait HandlesIslands
{
    protected $islands = [];

    public function island($name, $key, $mode = 'replace', $render = 'once', $defer = false, $lazy = false, $poll = null, $view = null)
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

        $placeholder = "{$view}_placeholder";

        if ($defer && $lazy) {
            throw new \InvalidArgumentException(
                "Cannot use both 'defer' and 'lazy' for island '{$name}'."
            );
        }

        if ($island = collect($this->islands)->get($key)) {
            return $island;
        }

        $island = new Island($name, $key, $view, $this, $mode, $render, $defer, $lazy, $poll, $placeholder);

        $this->islands[$island->key] = $island;

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
