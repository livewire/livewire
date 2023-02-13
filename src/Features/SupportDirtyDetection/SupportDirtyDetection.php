<?php

namespace Livewire\Features\SupportDirtyDetection;

use Livewire\ComponentHook;
use Illuminate\Support\Arr;

class SupportDirtyDetection extends ComponentHook
{
    function hydrate() {
        $hashMap = $this->generateAHashMapOfItsProperties(
            $this->getProperties()
        );

        $this->storeThisHashMapForLaterComparison($hashMap);
    }

    function update($propertyName) {
        return function () use ($propertyName) {
            $value = $this->getProperty($propertyName);

            $this->rehashProperty($propertyName, $value);
        };
    }

    function dehydrate($context) {
        if (! $this->storeHas('dirtyHashMap')) return;

        $hashMap = $this->generateAHashMapOfItsProperties($this->getProperties());

        $dirtyProperties = $this->compareThisHashMapAgainstTheEarlierOne($hashMap);

        $context->addEffect('dirty', $dirtyProperties);
    }

    function rehashProperty($key, $value)
    {
        $hashMap = $this->storeGet('dirtyHashMap');

        if (is_array($value)) {
            foreach (Arr::dot($value, $key.'.') as $dottedKey => $value) {
                $hashMap[$dottedKey] = crc32(json_encode($value));
            }
        } else {
            $hashMap[$key] = crc32(json_encode($value));
        }

        $this->storeThisHashMapForLaterComparison($hashMap);
    }

    function generateAHashMapOfItsProperties($properties)
    {
        $hashes = [];

        foreach ($properties as $key => $value) {
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

    function storeThisHashMapForLaterComparison($hashMap)
    {
        $this->storeSet('dirtyHashMap', $hashMap);
    }

    function compareThisHashMapAgainstTheEarlierOne($hashMap)
    {
        $earlierOne = $this->storeGet('dirtyHashMap');

        return array_keys(
            array_merge(
                array_diff_assoc($earlierOne, $hashMap),
                array_diff_assoc($hashMap, $earlierOne)
            )
        );
    }
}
