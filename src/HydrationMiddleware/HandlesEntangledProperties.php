<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Support\Arr;

class HandlesEntangledProperties implements HydrationMiddleware
{
    protected static $propertyHashesByComponentId = [];

    public static function hydrate($instance, $request)
    {
        $data = data_get($request, 'memo.data', []);

        collect($data)->each(function ($value, $key) use ($instance) {
            if (is_array($value)) {
                foreach (Arr::dot($value, $key . '.') as $dottedKey => $value) {
                    static::rehashProperty($dottedKey, $value, $instance);
                }
            } else {
                static::rehashProperty($key, $value, $instance);
            }
        });
    }

    public static function dehydrate($instance, $response)
    {
        self::handleEntanglementToParent($instance, $response);
        // self::handleEntanglementToChildren($instance, $response);
    }

    public static function handleEntanglementToParent($instance, $response)
    {
        $entangledToParent = data_get($response, 'memo.data.entangled', []);

        $dirtyEntangledProps = self::dirtyProps($instance, $response)
            ->intersectByKeys($entangledToParent)
            ->mapWithKeys(fn ($_, $key) => [$entangledToParent[$key] => data_get($instance, $key)])
            ->toArray();

        $instance->emitUp('fillEntangled', $dirtyEntangledProps);
    }

    public static function handleEntanglementToChildren($instance, $response)
    {
        $entangledByChildren = data_get($response, 'memo.data.entangledByChildren', []);

        $dirtyEntangledProps = self::dirtyProps($instance, $response)
            ->intersectByKeys($entangledByChildren)
            ->map(fn ($_, $key) => data_get($instance, $key))
            ->flatten()
            ->unique();

        // TODO: this probably doesn't work.
        $dirtyEntangledProps->each(fn ($id) => $instance->emitTo($id, 'refresh'));
    }

    public static function initialDehydrate($instance, $response)
    {
        $entangled = collect(data_get($response, 'memo.data.entangled', []))
            ->flip()
            ->map(fn ($_) => [$instance->id]);

        // TODO: this (emitUp) doesn't work.
        $instance->emitUp('registerEntangled', $entangled);
    }

    public static function dirtyProps($instance, $response)
    {
        // Only return the propertyHashes/props that have changed.
        return collect(static::$propertyHashesByComponentId[$instance->id] ?? [])
            ->filter(fn ($hash, $key) => static::hash(data_get($instance, $key)) !== $hash);
    }

    public static function rehashProperty($name, $value, $component)
    {
        static::$propertyHashesByComponentId[$component->id][$name] = static::hash($value);
    }

    public static function hash($value)
    {
        if (!is_null($value) && !is_string($value) && !is_numeric($value) && !is_bool($value)) {
            if (is_array($value)) {
                return json_encode($value);
            }
            $value = method_exists($value, '__toString')
                ? (string) $value
                : json_encode($value);
        }

        // Using crc32 because it's fast, and this doesn't have to be secure.
        return crc32($value);
    }
}
