<?php

namespace Livewire;

class EventBus
{
    protected $listeners = [];
    protected $listenersAfter = [];
    protected $listenersBefore = [];

    function boot()
    {
        app()->singleton($this::class);
    }

    function on($name, $callback) {
        if (! isset($this->listeners[$name])) $this->listeners[$name] = [];

        $this->listeners[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    function before($name, $callback) {
        if (! isset($this->listenersBefore[$name])) $this->listenersBefore[$name] = [];

        $this->listenersBefore[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    function after($name, $callback) {
        if (! isset($this->listenersAfter[$name])) $this->listenersAfter[$name] = [];

        $this->listenersAfter[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    function off($name, $callback) {
        $index = array_search($callback, $this->listeners[$name] ?? []);
        $indexAfter = array_search($callback, $this->listenersAfter[$name] ?? []);
        $indexBefore = array_search($callback, $this->listenersBefore[$name] ?? []);

        if ($index !== false) unset($this->listeners[$name][$index]);
        elseif ($indexAfter !== false) unset($this->listenersAfter[$name][$indexAfter]);
        elseif ($indexBefore !== false) unset($this->listenersBefore[$name][$indexBefore]);
    }

    function trigger($name, ...$params) {
        $middlewares = [];

        $listeners = array_merge(
            ($this->listenersBefore[$name] ?? []),
            ($this->listeners[$name] ?? []),
            ($this->listenersAfter[$name] ?? []),
        );

        foreach ($listeners as $callback) {
            $result = $callback(...$params);

            if ($result) {
                $middlewares[] = $result;
            }
        }

        return function (&$forward = null, ...$extras) use ($middlewares) {
            foreach ($middlewares as $finisher) {
                if ($finisher === null) continue;

                $finisher = is_array($finisher) ? last($finisher) : $finisher;

                $result = $finisher($forward, ...$extras);

                // Only overwrite previous "forward" if something is returned from the callback.
                $forward = $result ?? $forward;
            }

            return $forward;
        };
    }
}
