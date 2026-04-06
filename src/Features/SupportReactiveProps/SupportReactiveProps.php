<?php

namespace Livewire\Features\SupportReactiveProps;

use function Livewire\on;
use function Livewire\after;
use function Livewire\trigger;
use Illuminate\Database\Eloquent\Model;
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
        after('hydrate', function ($component) {
            $id = $component->getId();
            $updates = static::$pendingUpdates[$id] ?? [];
            unset(static::$pendingUpdates[$id]);

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

    static function shouldSkipUpdate($snapshot, $calls): bool
    {
        $id = $snapshot['memo']['id'] ?? null;
        $reactiveProps = $snapshot['memo']['props'] ?? [];

        // Only applies to components with #[Reactive] properties...
        if (empty($reactiveProps)) return false;

        // Only if parent already rendered and stored pending params...
        if (! isset(static::$pendingChildParams[$id])) return false;

        // Don't skip if component also has wire:model bindings...
        if (! empty($snapshot['memo']['bindings'] ?? [])) return false;

        // Don't skip if there are real method calls (not just $commit)...
        foreach ($calls as $call) {
            if (($call['method'] ?? '') !== '$commit') return false;
        }

        // Check each reactive prop for changes...
        $pendingParams = static::$pendingChildParams[$id];

        foreach ($reactiveProps as $propName) {
            $currentValue = $snapshot['data'][$propName] ?? null;
            $newValue = $pendingParams[$propName] ?? null;

            if (! static::valuesMatch($currentValue, $newValue)) {
                return false;
            }
        }

        return true;
    }

    protected static function valuesMatch($snapshotValue, $pendingValue): bool
    {
        // Handle Eloquent models: compare class + key identity...
        if ($pendingValue instanceof Model) {
            return static::modelMatchesSnapshot($snapshotValue, $pendingValue);
        }

        // For scalars and arrays, compare via CRC32 hash...
        return crc32(json_encode($snapshotValue)) === crc32(json_encode($pendingValue));
    }

    protected static function modelMatchesSnapshot($snapshotValue, Model $model): bool
    {
        // Dehydrated model format: [null, {class: '...', key: ..., s: 'mdl'}]
        if (! is_array($snapshotValue)) return false;

        $meta = $snapshotValue[1] ?? [];

        if (($meta['s'] ?? null) !== 'mdl') return false;

        $snapshotClass = $meta['class'] ?? null;

        // Compare class (handles morph aliases)...
        if ($snapshotClass !== get_class($model) && $snapshotClass !== $model->getMorphClass()) {
            return false;
        }

        // Compare primary key...
        if (($meta['key'] ?? null) != $model->getKey()) {
            return false;
        }

        // If the model has unsaved attribute changes, don't skip...
        if ($model->isDirty()) {
            return false;
        }

        // If the model was saved during this request, don't skip...
        if ($model->wasChanged()) {
            return false;
        }

        // If the model was just created during this request, don't skip...
        if ($model->wasRecentlyCreated) {
            return false;
        }

        return true;
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
