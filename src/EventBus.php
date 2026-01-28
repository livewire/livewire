<?php

declare(strict_types=1);

namespace Livewire;

class EventBus
{
    protected array $listeners = [];
    protected array $listenersAfter = [];
    protected array $listenersBefore = [];

    public function boot(): void
    {
        app()->singleton($this::class);
    }

    public function on(string $name, callable $callback): callable
    {
        $this->listeners[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    public function before(string $name, callable $callback): callable
    {
        $this->listenersBefore[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    public function after(string $name, callable $callback): callable
    {
        $this->listenersAfter[$name][] = $callback;

        return fn() => $this->off($name, $callback);
    }

    public function off(string $name, callable $callback): void
    {
        if (isset($this->listeners[$name])) {
            $index = array_search($callback, $this->listeners[$name], true);

            if ($index !== false) {
                unset($this->listeners[$name][$index]);
                return;
            }
        }

        if (isset($this->listenersAfter[$name])) {
            $index = array_search($callback, $this->listenersAfter[$name], true);

            if ($index !== false) {
                unset($this->listenersAfter[$name][$index]);
                return;
            }
        }

        if (isset($this->listenersBefore[$name])) {
            $index = array_search($callback, $this->listenersBefore[$name], true);

            if ($index !== false) {
                unset($this->listenersBefore[$name][$index]);
            }
        }
    }

    public function trigger(string $name, ...$params): callable
    {
        $middlewares = [];

        $listeners = [];

        if (isset($this->listenersBefore[$name])) {
            $listeners = $this->listenersBefore[$name];
        }

        if (isset($this->listeners[$name])) {
            $listeners = array_merge($listeners, $this->listeners[$name]);
        }

        if (isset($this->listenersAfter[$name])) {
            $listeners = array_merge($listeners, $this->listenersAfter[$name]);
        }

        foreach ($listeners as $callback) {
            $result = $callback(...$params);

            if ($result !== null) {
                $middlewares[] = $result;
            }
        }

        return function (&$forward = null, ...$extras) use ($middlewares) {
            foreach ($middlewares as $finisher) {
                if ($finisher === null) continue;

                $finisher = is_array($finisher) ? end($finisher) : $finisher;

                $result = $finisher($forward, ...$extras);

                $forward = $result ?? $forward;
            }

            return $forward;
        };
    }
}
