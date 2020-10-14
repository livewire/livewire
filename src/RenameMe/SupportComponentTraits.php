<?php

namespace Livewire\RenameMe;

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
                        $this->componentIdMethodMap[$component->id][$hook] = [$component, $method];
                    }
                }
            }

            $method = $this->componentIdMethodMap[$component->id]['hydrate'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, []);
        });

        Livewire::listen('component.mount', function ($component, $params) {
            $method = $this->componentIdMethodMap[$component->id]['mount'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, $params);
        });

        Livewire::listen('component.updating', function ($component, $name, $value) {
            $method = $this->componentIdMethodMap[$component->id]['updating'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, [$name, $value]);
        });

        Livewire::listen('component.updated', function ($component, $name, $value) {
            $method = $this->componentIdMethodMap[$component->id]['updated'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, [$name, $value]);
        });

        Livewire::listen('component.rendering', function ($component) {
            $method = $this->componentIdMethodMap[$component->id]['rendering'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, []);
        });

        Livewire::listen('component.rendered', function ($component, $view) {
            $method = $this->componentIdMethodMap[$component->id]['rendered'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, [$view]);
        });

        Livewire::listen('component.dehydrate', function ($component) {
            $method = $this->componentIdMethodMap[$component->id]['dehydrate'] ?? function () {};

            ImplicitlyBoundMethod::call(app(), $method, []);
        });
    }
}
