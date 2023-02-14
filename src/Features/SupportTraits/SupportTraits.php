<?php

namespace Livewire\Features\SupportTraits;

use function Livewire\wrap;
use function Livewire\on;
use function Livewire\before;
use Livewire\ComponentHook;

/**
 * Depends on: SupportLifecycleHooks to trigger "component." events
 */
class SupportTraits extends ComponentHook
{
    static function provide()
    {
        on('component.boot', function ($component) {
            static::callTraitSuffixedMethod($component, 'boot');
            static::callTraitSuffixedMethod($component, 'initialize');
        });

        on('component.booted', function ($component) {
            static::callTraitSuffixedMethod($component, 'booted');
        });

        on('component.hydrate', function ($component) {
            static::callTraitSuffixedMethod($component, 'hydrate');
        });

        on('component.mount', function ($component) {
            static::callTraitSuffixedMethod($component, 'mount');
        });

        on('render', function ($component, $view, $data) {
            static::callTraitSuffixedMethod($component, 'rendering');

            return function () use ($component, $view) {
                static::callTraitSuffixedMethod($component, 'rendered', [$view]);
            };
        });

        on('component.dehydrate', function ($component) {
            static::callTraitSuffixedMethod($component, 'dehydrate');
        });

        on('component.updating', function ($component, $name, $value) {
            static::callTraitSuffixedMethod($component, 'updating', [$name, $value]);
        });

        on('component.updated', function ($component, $name, $value) {
            static::callTraitSuffixedMethod($component, 'updated', [$name, $value]);
        });
    }

    static function callTraitSuffixedMethod($component, $name, $params = [])
    {
        foreach (class_uses_recursive($component) as $trait) {
            $method = $name.class_basename($trait);

            if (method_exists($component, $method)) {
                wrap($component)->$method(...$params);
            }
        }
    }
}
