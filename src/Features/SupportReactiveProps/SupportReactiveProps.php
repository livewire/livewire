<?php

namespace Livewire\Features\SupportReactiveProps;

use function Livewire\on;
use function Livewire\after;
use function Livewire\trigger;
use Livewire\ComponentHook;

class SupportReactiveProps extends ComponentHook
{
    public static $pendingChildParams = [];

    public static $pendingUpdates = [];

    static function provide()
    {
        on('flush-state', function () {
            static::$pendingChildParams = [];
            static::$pendingUpdates = [];
        });

        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            static::$pendingChildParams[$id] = $params;
        });

        // Fire updating*/updated* hooks after all hooks have hydrated
        // Values are already set in BaseReactive::hydrate() so lifecycle hooks see fresh data
        after('hydrate', function ($component, $memo) {
            $id = $component->getId();
            $updates = static::$pendingUpdates[$id] ?? [];
            unset(static::$pendingUpdates[$id]);

            // If this component was sent as a reactive child but no props
            // actually changed, skip its render to avoid wasted work.
            // Check memo['props'] to ensure we only do this for components
            // with #[Reactive] properties, not wire:model children...
            $hasReactiveProps = ! empty($memo['props'] ?? []);

            if ($hasReactiveProps && isset(static::$pendingChildParams[$id]) && empty($updates)) {
                $component->skipRender();
            }

            foreach ($updates as $update) {
                ['property' => $property, 'oldValue' => $oldValue, 'value' => $value, 'setValue' => $setValue] = $update;

                // Temporarily restore old value so updating* hooks see the previous state
                $setValue($oldValue);

                // Trigger updating* hooks (they see the old value via $this->property)
                $finish = trigger('update', $component, $property, $value);

                // Restore the new value for updated* hooks
                $setValue($value);

                // Trigger updated* hooks (they see the new value)
                $finish();
            }
        });
    }

    static function hasPassedInProps($id) {
        return isset(static::$pendingChildParams[$id]);
    }

    static function getPassedInProp($id, $name) {
        $params = static::$pendingChildParams[$id] ?? [];

        return $params[$name] ?? null;
    }

    static function queueUpdate($id, $property, $oldValue, $value, $setValue) {
        static::$pendingUpdates[$id][] = [
            'property' => $property,
            'oldValue' => $oldValue,
            'value' => $value,
            'setValue' => $setValue,
        ];
    }
}
