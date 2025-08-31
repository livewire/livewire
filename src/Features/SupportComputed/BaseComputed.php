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
        public $key = null,
        public $tags = null,
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
        if ($this->generatePropertyName($property) !== $this->getName()) return;

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
        $handlerClass = DefaultPersistHandler::class;

        if (is_string($this->persist) && class_exists($this->persist)) {
            $handlerClass = $this->persist;
        }

        return (new $handlerClass($this))->handleGet();
    }

    protected function handleCachedGet()
    {
        $handlerClass = DefaultCacheHandler::class;

        if (is_string($this->cache) && class_exists($this->cache)) {
            $handlerClass = $this->cache;
        }

        return (new $handlerClass($this))->handleGet();
    }

    protected function handlePersistedUnset()
    {
        $handlerClass = DefaultPersistHandler::class;

        if (is_string($this->persist) && class_exists($this->persist)) {
            $handlerClass = $this->persist;
        }

        (new $handlerClass($this))->handleUnset();
    }

    protected function handleCachedUnset()
    {
        $handlerClass = DefaultCacheHandler::class;

        if (is_string($this->cache) && class_exists($this->cache)) {
            $handlerClass = $this->cache;
        }

        (new $handlerClass($this))->handleUnset();
    }


    public function evaluateComputed()
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
