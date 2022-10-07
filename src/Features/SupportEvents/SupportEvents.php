<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Mechanisms\ComponentDataStore;
use Livewire\Synthesizers\LivewireSynth;
use function Synthetic\wrap;

class SupportEvents
{
    function boot()
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $listeners = static::getListenerEventNames($target);
            $emits = $this->getServerEmittedEvents($target);
            $dispatches = $this->getServerDispatchedEvents($target);

            $listeners && $context->addEffect('listeners', $listeners);
            $emits && $context->addEffect('emits', $emits);
            $dispatches && $context->addEffect('dispatches', $dispatches);
        });

        app('synthetic')->on('methods', function ($target, $addMethod) {
            $addMethod('__emit');
        });
    }

    static function receive($component, $name, $params)
    {
        $names = static::getListenerEventNames($component);

        if (! in_array($name, $names)) {
            throw new \Exception('EventHandlerDoesNotExist'); // @todo...
        }

        $method = static::getListenerMethodName($component, $name);

        wrap($component)->$method(...$params);
    }

    static function getListenerEventNames($component)
    {
        $listeners = $component->__getListeners();

        return collect($listeners)
            ->map(fn ($value, $key) => is_numeric($key) ? $value : $key)
            ->values()
            ->toArray();
    }

    static function getListenerMethodName($component, $name)
    {
        $listeners = $component->__getListeners();

        foreach ($listeners as $event => $method) {
            if (is_numeric($event)) $event = $method;

            if ($name === $event) return $method;
        }

        throw new \Exception('Event method not found');
    }

    static function emit($component, $event, ...$params)
    {
        $event = new Event($event, $params);

        ComponentDataStore::push($component, 'emitted', $event);

        return $event;
    }

    static function dispatch($component, $event, $data)
    {
        ComponentDataStore::push($component, 'dispatched', [
            'event' => $event,
            'data' => $data,
        ]);
    }

    function getServerEmittedEvents($component)
    {
        return collect(ComponentDataStore::get($component, 'emitted', []))
            ->map(fn ($event) => $event->serialize())
            ->toArray();
    }

    function getServerDispatchedEvents($component)
    {
        return ComponentDataStore::get($component, 'dispatched', []);
    }

    static function dispatchBrowserEvent($component, $event, $data = null)
    {
        ComponentDataStore::push($component, 'dispatched', [
            'event' => $event,
            'data' => $data,
        ]);
    }
}
