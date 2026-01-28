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

        // Process queued reactive prop updates AFTER all hooks have hydrated
        // This ensures SupportLifecycleHooks is initialized before we trigger updates
        after('hydrate', function ($component) {
            $id = $component->getId();
            $updates = static::$pendingUpdates[$id] ?? [];
            unset(static::$pendingUpdates[$id]);

            foreach ($updates as $update) {
                ['property' => $property, 'value' => $value, 'setValue' => $setValue] = $update;

                // Trigger updating* hooks (they see the old value)
                $finish = trigger('update', $component, $property, $value);

                // Set the new value on the component
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

    static function queueUpdate($id, $property, $value, $setValue) {
        static::$pendingUpdates[$id][] = [
            'property' => $property,
            'value' => $value,
            'setValue' => $setValue,
        ];
    }
}
