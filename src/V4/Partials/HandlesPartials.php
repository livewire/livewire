<?php

namespace Livewire\V4\Partials;

use Livewire\Drawer\Utils;

trait HandlesPartials
{
    protected $partials = [];

    public function partial($name, $view = null, $data = []): Partial
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

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this);

        $partial = new Partial($name, $view, array_merge($componentData, $data));

        $this->partials[] = $partial;

        $this->skipRender();

        return $partial;
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