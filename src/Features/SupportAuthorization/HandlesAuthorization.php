<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    public function authorizeFromAttribute($method, $ability, $argument, $parameters)
    {
        if (is_null($argument)) {
            // Check if its regular ability name, not class name
            if (is_string($ability) && ! str_contains($ability, '\\')) {
                return $this->authorize($ability);
            }

            return $this->authorize($this->normalizeGuessedAbilityName($method), $ability);
        }

        // Resolve method dependencies lazily, then reuse them for multi-argument authorization checks...
        $methodDependencies = null;
        $resolveMethodDependencies = function () use ($method, &$methodDependencies, $parameters): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this, $method],
                $parameters,
            );
        };

        $resolved = [];
        foreach (Arr::wrap($argument) as $arg) {
            $resolved[] = $this->resolveArgument($arg, $method, $resolveMethodDependencies);
        }

        return $this->authorize($ability, $resolved);
    }

    protected function resolveArgument(string $arg, string $method, \Closure $resolveMethodDependencies): mixed
    {
        // Action that does not require a model, for example a 'create' action...
        if (class_exists($arg)) {
            return $arg;
        }

        // Try method parameter first (prioritized per rules)
        $methodArgument = Arr::first(
            (new \ReflectionObject($this))->getMethod($method)->getParameters(),
            fn (\ReflectionParameter $parameter): bool => $parameter->getName() === $arg,
        );

        if ($methodArgument instanceof \ReflectionParameter) {
            $methodDependencies = $resolveMethodDependencies();

            return $methodDependencies['named'][$arg];
        }

        // Fall back to component property
        return data_get($this, $arg);
    }
}