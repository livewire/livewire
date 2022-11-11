<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\on;
use function Livewire\store;
use function Livewire\wrap;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;

class SupportEvents
{
    function boot()
    {
        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $listeners = static::getListenerEventNames($target);
            $emits = $this->getServerEmittedEvents($target);
            $dispatches = $this->getServerDispatchedEvents($target);

            $listeners && $context->addEffect('listeners', $listeners);
            $emits && $context->addEffect('emits', $emits);
            $dispatches && $context->addEffect('dispatches', $dispatches);
        });

        on('methods', function ($target, $addMethod) {
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

        store($component)->push('emitted', $event);

        return $event;
    }

    static function dispatch($component, $event, $data)
    {
        store($component)->push('dispatched', [
            'event' => $event,
            'data' => $data,
        ]);
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

    static function dispatchBrowserEvent($component, $event, $data = null)
    {
        store($component)->push('dispatched', [
            'event' => $event,
            'data' => $data,
        ]);
    }
}
