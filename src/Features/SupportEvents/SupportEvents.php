<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\store;
use function Livewire\wrap;

use Livewire\ComponentHook;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;

class SupportEvents extends ComponentHook
{
    function boot()
    {
        // @todo: refactor this out. Ew.
        on('methods', function ($target, $addMethod) {
            if ($target !== $this->component) return;

            $addMethod('__emit');
        });
    }

    function call($method, $params, $returnEarly)
    {
        if ($method === '__emit') {
            $name = array_shift($params);

            $names = static::getListenerEventNames($this->component);

            if (! in_array($name, $names)) {
                throw new \Exception('EventHandlerDoesNotExist'); // @todo...
            }

            $method = static::getListenerMethodName($this->component, $name);

            wrap($this->component)->$method(...$params);

            $returnEarly();
        }
    }

    function dehydrate($context)
    {
        $listeners = static::getListenerEventNames($this->component);
        $emits = $this->getServerEmittedEvents($this->component);
        $dispatches = $this->getServerDispatchedEvents($this->component);

        $listeners && $context->addEffect('listeners', $listeners);
        $emits && $context->addEffect('emits', $emits);
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

    function getServerEmittedEvents($component)
    {
        return collect(store($component)->get('emitted', []))
            ->map(fn ($event) => $event->serialize())
            ->toArray();
    }

    function getServerDispatchedEvents($component)
    {
        return store($component)->get('dispatched', []);
    }
}
