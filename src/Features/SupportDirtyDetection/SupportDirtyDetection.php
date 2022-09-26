<?php

namespace Livewire\Features\SupportDirtyDetection;

use Livewire\Mechanisms\ComponentDataStore;
use Illuminate\Support\Arr;

class SupportDirtyDetection
{
    function boot()
    {
        $this->whenAComponentIsHydrated(function ($component) {
            $hashMap = $this->generateAHashMapOfItsProperties($component);

            $this->storeThisHashMapForLaterComparison($component, $hashMap);
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
        app('synthetic')->on('hydrated', function ($target) use ($callback) {
            if (! $target instanceof \Livewire\Component) return;

            return function () use ($callback, $target) {
                $callback($target);
            };
        });
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
        ComponentDataStore::set($component, 'dirtyHashMap', $hashMap);
    }

    function whenAComponentIsDehydrated($callback)
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) use ($callback) {
            if (! $synth instanceof \Livewire\Synthesizers\LivewireSynth) return;

            $callback($target, function ($dirtyProperties) use ($context) {
                $context->addEffect('dirty', $dirtyProperties);
            });
        });
    }

    function onlyIfItWasHydratedEarlier($component, $callback)
    {
        ComponentDataStore::has($component, 'dirtyHashMap') && $callback();
    }

    function compareThisHashMapAgainstTheEarlierOne($component, $hashMap)
    {
        $earlierOne = ComponentDataStore::get($component, 'dirtyHashMap');

        return array_keys(
            array_diff($earlierOne, $hashMap)
        );
    }
}
