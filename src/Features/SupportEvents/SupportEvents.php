<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\store;
use function Livewire\wrap;

use Livewire\ComponentHook;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\HandleComponents\Synthesizers\LivewireSynth;

class SupportEvents extends ComponentHook
{
    function call($method, $params, $returnEarly)
    {
        if ($method === '__dispatch') {
            [$name, $params] = $params;

            $names = static::getListenerEventNames($this->component);

            if (! in_array($name, $names)) {
                throw new \Exception('EventHandlerDoesNotExist'); // @todo...
            }

            $method = static::getListenerMethodName($this->component, $name);

            $returnEarly(
                wrap($this->component)->$method(...$params)
            );
        }
    }

    function dehydrate($context)
    {
        if ($context->mounting) {
            $listeners = static::getListenerEventNames($this->component);

            $listeners && $context->addEffect('listeners', $listeners);
        }

        $dispatches = $this->getServerDispatchedEvents($this->component);

        $dispatches && $context->addEffect('dispatches', $dispatches);
    }

    static function getListenerEventNames($component)
    {
        $listeners = static::getComponentListeners($component);

        return collect($listeners)
            ->map(fn ($value, $key) => is_numeric($key) ? $value : $key)
            ->values()
            ->toArray();
    }

    static function getListenerMethodName($component, $name)
    {
        $listeners = static::getComponentListeners($component);

        foreach ($listeners as $event => $method) {
            if (is_numeric($event)) $event = $method;

            if ($name === $event) return $method;
        }

        throw new \Exception('Event method not found');
    }

    static function getComponentListeners($component)
    {
        $fromClass = invade($component)->getListeners();

        $fromAttributes = store($component)->get('listenersFromPropertyHooks', []);

        return array_merge($fromClass, $fromAttributes);
    }

    function getServerDispatchedEvents($component)
    {
        return collect(store($component)->get('dispatched', []))
            ->map(fn ($event) => $event->serialize())
            ->toArray();
    }
}
