<?php

namespace Livewire\Features\SupportComputed;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\off;

use Livewire\Features\SupportAttributes\Attribute;
use Illuminate\Support\Facades\Cache;

#[\Attribute]
class BaseComputed extends Attribute
{
    protected $requestCachedValue;

    function __construct(
        public $persist = false,
        public $seconds = 3600, // 1 hour...
        public $cache = false,
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

        if ($this->cache) {
            $returnValue($this->handleCachedGet());

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

        if ($this->cache) {
            $this->handleCachedUnset();

            return;
        }

        unset($this->requestCachedValue);
    }

    protected function handlePersistedGet()
    {
        $key = $this->generatePersistedKey();

        return Cache::remember($key, $this->seconds, function () {
            return $this->evaluateComputed();
        });
    }

    protected function handleCachedGet()
    {
        $key = $this->generateCachedKey();

        return Cache::remember($key, $this->seconds, function () {
            return $this->evaluateComputed();
        });
    }

    protected function handlePersistedUnset()
    {
        $key = $this->generatePersistedKey();

        Cache::forget($key);
    }

    protected function handleCachedUnset()
    {
        $key = $this->generateCachedKey();

        Cache::forget($key);
    }

    protected function generatePersistedKey()
    {
        return 'lw_computed.'.$this->component->getId().'.'.$this->getName();
    }

    protected function generateCachedKey()
    {
        return 'lw_computed.'.$this->component->getName().'.'.$this->getName();
    }

    protected function evaluateComputed()
    {
        return invade($this->component)->{$this->getName()}();
    }
}
