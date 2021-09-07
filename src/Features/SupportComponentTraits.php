<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Livewire\ImplicitlyBoundMethod;

class SupportComponentTraits
{
    static function init() { return new static; }

    protected $componentIdMethodMap = [];

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component) {
            $component->initializeTraits();

            foreach (class_uses_recursive($component) as $trait) {
                $hooks = [
                    'hydrate',
                    'mount',
                    'updating',
                    'updated',
                    'rendering',
                    'rendered',
                    'dehydrate',
                ];

                foreach ($hooks as $hook) {
                    $method = $hook.class_basename($trait);

                    if (method_exists($component, $method)) {
                        $this->componentIdMethodMap[$component->id][$hook][] = [$component, $method];
                    }
                }
            }

            $methods = $this->componentIdMethodMap[$component->id]['hydrate'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method);
            }
        });

        Livewire::listen('component.mount', function ($component, $params) {
            $methods = $this->componentIdMethodMap[$component->id]['mount'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method, $params);
            }
        });

        Livewire::listen('component.updating', function ($component, $name, $value) {
            $methods = $this->componentIdMethodMap[$component->id]['updating'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method, [$name, $value]);
            }
        });

        Livewire::listen('component.updated', function ($component, $name, $value) {
            $methods = $this->componentIdMethodMap[$component->id]['updated'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method, [$name, $value]);
            }
        });

        Livewire::listen('component.rendering', function ($component) {
            $methods = $this->componentIdMethodMap[$component->id]['rendering'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method);
            }
        });

        Livewire::listen('component.rendered', function ($component, $view) {
            $methods = $this->componentIdMethodMap[$component->id]['rendered'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method, [$view]);
            }
        });

        Livewire::listen('component.dehydrate', function ($component) {
            $methods = $this->componentIdMethodMap[$component->id]['dehydrate'] ?? [];

            foreach ($methods as $method) {
                ImplicitlyBoundMethod::call(app(), $method);
            }
        });
    }
}
