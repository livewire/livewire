<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Livewire\ImplicitlyBoundMethod;

class SupportComponentTraits
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component) {
            $component->initializeTraits();

            $this->callHook('hydrate', $component);
        });

        Livewire::listen('component.mount', function ($component, $params) {
            $this->callHook('hydrate', $component, $params);
        });

        Livewire::listen('component.updating', function ($component, $name, $value) {
            $this->callHook('updating', $component, [$name, $value]);
        });

        Livewire::listen('component.updated', function ($component, $name, $value) {
            $this->callHook('updated', $component, [$name, $value]);
        });

        Livewire::listen('component.rendering', function ($component) {
            $this->callHook('rendering', $component);
        });

        Livewire::listen('component.rendered', function ($component, $view) {
            $this->callHook('rendered', $component, [$view]);
        });

        Livewire::listen('component.dehydrate', function ($component) {
            $this->callHook('dehydrate', $component);
        });
    }

    protected function callHook($hook, $component, $params = [])
    {
        foreach (class_uses_recursive($component) as $trait) {
            $method = $hook.class_basename($trait);

            if (method_exists($component, $method)) {
                ImplicitlyBoundMethod::call(app(), [$component, $method], $params);
            }
        }
    }
}
