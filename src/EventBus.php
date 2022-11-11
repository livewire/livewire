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

    function trigger($name, &...$params) {
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

        // Here's we're returning an anonymous class that behaves both
        // as a function and also optionally as an array to be destructed.
        // This way we can support single and multiple middlware returns.
        return new class ($middlewares) implements \ArrayAccess {
            function __construct(public $middlewares) {}

            function __invoke(&$forward = null)
            {
                return $this->runThroughMiddlewares($this->middlewares, $forward);
            }

            public $accessedOffsets = [];

            function offsetGet($key): mixed
            {
                $this->accessedOffsets[] = $key;

                $this->accessedOffsets = array_unique($this->accessedOffsets);

                return function (&$forward) use ($key) {
                    $currentOffset = array_shift($this->accessedOffsets);

                    if (! $currentOffset === $key) {
                        throw new \Exception('Calling middlewares out of order...');
                    }

                    // This is the last call...
                    if (count($this->accessedOffsets) === 0) {
                        return $this->runThroughMiddlewares($this->middlewares, $forward);
                    }

                    foreach ($this->middlewares as $index => $finisher) {
                        if ($finisher === null) {
                            unset($this->middlewares[$index]);
                            return;
                        }

                        $this->middlewares[$index] = $finisher($forward);
                    }
                };

                $middlewares = collect($this->middlewares)->pluck($key);

                return function (&$forward = null) use ($middlewares) {
                    return $this->runThroughMiddlewares($middlewares, $forward);
                };
            }

            function runThroughMiddlewares($middlewares, &$forward) {
                foreach ($middlewares as $finisher) {
                    if ($finisher === null) continue;

                    $finisher = is_array($finisher) ? last($finisher) : $finisher;

                    $result = $finisher($forward);

                    // Only overwrite previous "forward" if something is returned from the callback.
                    $forward = $result === null ? $forward : $result;
                }

                return $forward;
            }

            function offsetExists($key): bool
            {
                return isset($this->middlewares[$key]);
            }

            function offsetSet($key, $value): void
            {
                //
            }

            function offsetUnset($key): void
            {
                //
            }
        };
    }
}
