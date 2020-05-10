<?php

namespace Livewire\Macros;

use Livewire\Livewire;
use Livewire\LivewireController;

class RouterMacros
{
    public function layout()
    {
        return function ($layout) {
            return (new RouteRegistrarWithAllowedAttributes($this))
                ->allowAttributes('layout', 'section')
                ->layout($layout);
        };
    }

    public function section()
    {
        return function ($section) {
            return (new RouteRegistrarWithAllowedAttributes($this))
                ->allowAttributes('layout', 'section')
                ->section($section);
        };
    }

    public function livewire()
    {
        return function ($uri, $component = null) {
            $component = $component ?: $uri;

            return $this->get($uri, [LivewireController::class, $component]);
        };
    }
}
