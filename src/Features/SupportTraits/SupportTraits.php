<?php

namespace Livewire\Features\SupportTraits;

use function Synthetic\on;
use function Synthetic\wrap;

/**
 * Depends on: SupportLifecycleHooks to trigger "component." events
 */
class SupportTraits
{
    function boot()
    {
        on('component.boot', function ($component) {
            $this->callTraitSuffixedMethod($component, 'boot');
            $this->callTraitSuffixedMethod($component, 'initialize');
        });

        on('component.booted', function ($component) {
            $this->callTraitSuffixedMethod($component, 'booted');
        });

        on('component.hydrate', function ($component) {
            $this->callTraitSuffixedMethod($component, 'hydrate');
        });

        on('component.mount', function ($component) {
            $this->callTraitSuffixedMethod($component, 'mount');
        });

        on('render', function ($component, $view, $data) {
            $this->callTraitSuffixedMethod($component, 'rendering');

            return function () use ($component, $view) {
                $this->callTraitSuffixedMethod($component, 'rendered', [$view]);
            };
        });

        on('component.dehydrate', function ($component) {
            $this->callTraitSuffixedMethod($component, 'dehydrate');
        });

        on('component.updating', function ($component, $name, $value) {
            $this->callTraitSuffixedMethod($component, 'updating', [$name, $value]);
        });

        on('component.updated', function ($component, $name, $value) {
            $this->callTraitSuffixedMethod($component, 'updated', [$name, $value]);
        });
    }

    function callTraitSuffixedMethod($component, $name, $params = [])
    {
        foreach (class_uses_recursive($component) as $trait) {
            $method = $name.class_basename($trait);

            if (method_exists($component, $method)) {
                wrap($component)->$method(...$params);
            }
        }
    }
}
