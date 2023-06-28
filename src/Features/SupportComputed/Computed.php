<?php

namespace Livewire\Features\SupportComputed;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\off;

use Livewire\Features\SupportAttributes\Attribute;
use Illuminate\Support\Facades\Cache;

#[\Attribute]
class Computed extends Attribute
{
    protected $requestCachedValue;

    function __construct(
        public $persist = false,
        public $seconds = 3600, // 1 hour...
    ) {}

    function boot()
    {
        off('__get', $this->handleMagicGet(...));
        on('__get', $this->handleMagicGet(...));

        off('__unset', $this->handleMagicUnset(...));
        on('__unset', $this->handleMagicUnset(...));
    }

    function call()
    {
        throw new CannotCallComputedDirectlyException(
            $this->component->getName(),
            $this->getName(),
        );
    }

    protected function handleMagicGet($target, $property, $returnValue)
    {
        if ($target !== $this->component) return;
        if ($property !== $this->getName()) return;

        if ($this->persist) {
            $returnValue($this->handlePersistedGet());

            return;
        }

        $returnValue(
            $this->requestCachedValue ??= $this->evaluateComputed()
        );
    }

    protected function handleMagicUnset($target, $property)
    {
        if ($target !== $this->component) return;
        if ($property !== $this->getName()) return;

        if ($this->persist) {
            $this->handlePersistedUnset();

            return;
        }

        unset($this->requestCachedValue);
    }

    protected function handlePersistedGet()
    {
        $key = $this->generateCacheKey();

        return Cache::remember($key, $this->seconds, function () {
            return $this->evaluateComputed();
        });
    }

    protected function handlePersistedUnset()
    {
        $key = $this->generateCacheKey();

        Cache::forget($key);
    }

    protected function generateCacheKey()
    {
        return 'lw_computed.'.$this->component->getId().'.'.$this->getName();
    }

    protected function evaluateComputed()
    {
        return invade($this->component)->{$this->getName()}();
    }
}
