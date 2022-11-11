<?php

namespace Livewire\Features\SupportDirtyDetection;

use function Livewire\before;
use function Livewire\store;
use function Livewire\on;
use Livewire\Mechanisms\DataStore;

use Livewire\Drawer\Utils;
use Illuminate\Support\Arr;

class SupportDirtyDetection
{
    function boot()
    {
        $this->whenAComponentIsHydrated(function ($component) {
            $hashMap = $this->generateAHashMapOfItsProperties($component);

            $this->storeThisHashMapForLaterComparison($component, $hashMap);
        });

        $this->whenComponentDataIsUpdated(function ($component, $property) {
            $value = $component->$property;

            $this->rehashProperty($component, $property, $value);
        });

        $this->whenAComponentIsDehydrated(function ($component, $addDirtyPropertiesToPayload) {
            $this->onlyIfItWasHydratedEarlier($component, function () use ($component, $addDirtyPropertiesToPayload) {
                $hashMap = $this->generateAHashMapOfItsProperties($component);

                $dirtyProperties = $this->compareThisHashMapAgainstTheEarlierOne($component, $hashMap);

                $addDirtyPropertiesToPayload($dirtyProperties);
            });
        });
    }

    function whenAComponentIsHydrated($callback)
    {
        on('hydrate', function ($synth, $rawValue, $meta) use ($callback) {
            if (! $synth instanceof \Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth) return;

            return function ($target) use ($callback) {
                $callback($target);
            };
        });
    }

    function whenComponentDataIsUpdated($callback)
    {
        before('update', function ($target, $path, $value) use ($callback) {
            if (! $target instanceof \Livewire\Component) return;

            return function ($newValue) use ($target, $path, $callback) {
                $property = Utils::beforeFirstDot($path);

                $callback($target, $property);
            };
        });
    }

    function rehashProperty($component, $key, $value)
    {
        $hashMap = store($component)->get('dirtyHashMap');

        if (is_array($value)) {
            foreach (Arr::dot($value, $key.'.') as $dottedKey => $value) {
                $hashMap[$dottedKey] = crc32(json_encode($value));
            }
        } else {
            $hashMap[$key] = crc32(json_encode($value));
        }

        $this->storeThisHashMapForLaterComparison($component, $hashMap);
    }

    function generateAHashMapOfItsProperties($component)
    {
        $hashes = [];

        foreach ($component->all() as $key => $value) {
            if (is_array($value)) {
                foreach (Arr::dot($value, $key.'.') as $dottedKey => $value) {
                    $hashes[$dottedKey] = crc32(json_encode($value));
                }
            } else {
                $hashes[$key] = crc32(json_encode($value));
            }
        }

        return $hashes;
    }

    function storeThisHashMapForLaterComparison($component, $hashMap)
    {
        store($component)->set('dirtyHashMap', $hashMap);
    }

    function whenAComponentIsDehydrated($callback)
    {
        on('dehydrate', function ($synth, $target, $context) use ($callback) {
            if (! $synth instanceof \Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth) return;

            $callback($target, function ($dirtyProperties) use ($context) {
                $context->addEffect('dirty', $dirtyProperties);
            });
        });
    }

    function onlyIfItWasHydratedEarlier($component, $callback)
    {
        store($component)->has('dirtyHashMap') && $callback();
    }

    function compareThisHashMapAgainstTheEarlierOne($component, $hashMap)
    {
        $earlierOne = store($component)->get('dirtyHashMap');

        return array_keys(
            array_diff_assoc($earlierOne, $hashMap)
        );
    }
}
