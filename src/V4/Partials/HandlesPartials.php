<?php

namespace Livewire\V4\Partials;

use Livewire\Drawer\Utils;

trait HandlesPartials
{
    protected $partials = [];

    // @todo: hack...
    public $isSubsequentRequest = false;

    public function renderPartial($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        return $this->partial($name, $view, $data, $mode, $defer, $fromBladeDirective);
    }

    public function partial($name, $view = null, $data = [], $mode = null, $defer = false, $fromBladeDirective = false)
    {
        // If second parameter is an array, treat it as data and use lookup for view
        if (is_array($view)) {
            $data = $view;
            $view = null;
        }

        // If no view provided, try to look it up from compiled partials
        if ($view === null && property_exists($this, 'partialLookup') && isset($this->partialLookup[$name])) {
            $view = $this->partialLookup[$name];
        }

        // If still no view, throw helpful error
        if ($view === null) {
            throw new \InvalidArgumentException(
                "No view specified for partial '{$name}'. " .
                "Either provide a view name or define the partial inline in your component."
            );
        }

        if ($mode === null) {
            $mode = ($this->isSubsequentRequest && $fromBladeDirective) ? 'skip' : 'replace';
        }

        if ($fromBladeDirective) {
            if ($this->isSubsequentRequest) {
                if ($mode === 'skip') {
                    return new SkippedPartial($name);
                }
            } else {
                if ($defer || $mode === 'defer') {
                    return new DeferredPartial($name);
                } elseif ($mode === 'lazy') {
                    return new LazyPartial($name);
                }
            }
        }

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this);

        $partial = new Partial($name, $view, array_merge($componentData, $data), $this, $mode);

        if (! $fromBladeDirective) {
            $this->skipRender();

            $this->partials[] = $partial;
        }

        return $partial;
    }

    // This method is called from the frontend via: wire:click="$partial('name...')"
    // This method has been whitelisted in HandleComponents.php
    public function __partial($name)
    {
        $this->partial($name);
    }

    public function getPartials(): array
    {
        return $this->partials;
    }

    public function popLastPartial()
    {
        return array_pop($this->partials);
    }
}