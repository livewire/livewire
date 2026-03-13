<?php

namespace Livewire\Features\SupportComputed;

use function Livewire\invade;

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
        public $key = null,
        public $tags = null,
    ) {}

    function call()
    {
        throw new CannotCallComputedDirectlyException(
            $this->component->getName(),
            $this->getName(),
        );
    }

    public function handleMagicGet($returnValue)
    {
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

    public function handleMagicUnset()
    {
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

        $closure = fn () => $this->evaluateComputed();

        return match(Cache::supportsTags() && !empty($this->tags)) {
            true => Cache::tags($this->tags)->remember($key, $this->seconds, $closure),
            default => Cache::remember($key, $this->seconds, $closure)
        };
    }

    protected function handleCachedGet()
    {
        $key = $this->generateCachedKey();

        $closure = fn () => $this->evaluateComputed();

        return match(Cache::supportsTags() && !empty($this->tags)) {
            true => Cache::tags($this->tags)->remember($key, $this->seconds, $closure),
            default => Cache::remember($key, $this->seconds, $closure)
        };
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
        if ($this->key) return $this->key;

        return 'lw_computed.'.$this->component->getId().'.'.$this->getName();
    }

    protected function generateCachedKey()
    {
        if ($this->key) return $this->key;

        return 'lw_computed.'.$this->component->getName().'.'.$this->getName();
    }

    protected function evaluateComputed()
    {
        return invade($this->component)->{parent::getName()}();
    }

    public function getName()
    {
        return $this->generatePropertyName(parent::getName());
    }

    private function generatePropertyName($value)
    {
        return str($value)->camel()->toString();
    }


}
