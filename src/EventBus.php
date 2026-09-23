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

    protected static $emptyFinisher;

    public function off(string $name, callable $callback): void
    {
        if (isset($this->listeners[$name])) {
            $index = array_search($callback, $this->listeners[$name], true);

            if ($index !== false) {
                unset($this->listeners[$name][$index]);
                if (empty($this->listeners[$name])) unset($this->listeners[$name]);
                return;
            }
        }

        if (isset($this->listenersAfter[$name])) {
            $index = array_search($callback, $this->listenersAfter[$name], true);

            if ($index !== false) {
                unset($this->listenersAfter[$name][$index]);
                if (empty($this->listenersAfter[$name])) unset($this->listenersAfter[$name]);
                return;
            }
        }

        if (isset($this->listenersBefore[$name])) {
            $index = array_search($callback, $this->listenersBefore[$name], true);

            if ($index !== false) {
                unset($this->listenersBefore[$name][$index]);
                if (empty($this->listenersBefore[$name])) unset($this->listenersBefore[$name]);
            }
        }
    }

    public function trigger(string $name, ...$params): callable
    {
        $hasBefore = isset($this->listenersBefore[$name]);
        $hasMain = isset($this->listeners[$name]);
        $hasAfter = isset($this->listenersAfter[$name]);

        if (! $hasBefore && ! $hasMain && ! $hasAfter) {
            return static::$emptyFinisher ??= static fn (&$forward = null) => $forward;
        }

        $middlewares = [];

        if ($hasBefore) {
            foreach ($this->listenersBefore[$name] as $callback) {
                $result = $callback(...$params);
                if ($result !== null) {
                    $middlewares[] = $result;
                }
            }
        }

        if ($hasMain) {
            foreach ($this->listeners[$name] as $callback) {
                $result = $callback(...$params);
                if ($result !== null) {
                    $middlewares[] = $result;
                }
            }
        }

        if ($hasAfter) {
            foreach ($this->listenersAfter[$name] as $callback) {
                $result = $callback(...$params);
                if ($result !== null) {
                    $middlewares[] = $result;
                }
            }
        }

        if (empty($middlewares)) {
            return static::$emptyFinisher ??= static fn (&$forward = null) => $forward;
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
