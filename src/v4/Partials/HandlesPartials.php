<?php

namespace Livewire\V4\Partials;

use Livewire\Drawer\Utils;

trait HandlesPartials
{
    protected $partials = [];

    public function partial($name, $view, $data = []): Partial
    {
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
}