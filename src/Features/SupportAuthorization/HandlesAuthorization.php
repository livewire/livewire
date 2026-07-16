<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    protected ?string $method = null;
    protected ?array $parameters = null;

    public function setMethodAndParameters($method, $parameters): void
    {
        $this->method = $method;
        $this->parameters = $parameters;
    }

    public function handleAuthorization($ability, $argument): Response
    {
        if (! $this->method && ! $this->parameters) {
            return $this->authorize($ability, $argument);
        }

        // Action that does not require a model or class...
        if (is_null($argument)) {
            return $this->authorize($ability);
        }

        $arguments = Arr::wrap($argument);

        // Resolve method dependencies lazily, then reuse them for multi-argument authorization checks...
        $methodDependencies = null;
        $resolveMethodDependencies = function () use (&$methodDependencies): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this, $this->method],
                $this->parameters,
            );
        };

        // Resolve each argument (prioritize method parameters first, then component properties)
        $resolved = [];
        foreach ($arguments as $arg) {
            $resolved[] = $this->resolveArgument($arg, $resolveMethodDependencies);
        }

        return $this->authorize($ability, $resolved);
    }

    protected function resolveArgument(string $arg, \Closure $resolveMethodDependencies): mixed
    {
        // Action that does not require a model, for example a 'create' action...
        if (class_exists($arg)) {
            return $arg;
        }

        // Try method parameter first (prioritized per rules)
        $methodArgument = Arr::first(
            (new \ReflectionObject($this))->getMethod($this->method)->getParameters(),
            fn (\ReflectionParameter $parameter): bool => $parameter->getName() === $arg,
        );

        if ($methodArgument instanceof \ReflectionParameter) {
            $methodDependencies = $resolveMethodDependencies();

            return $methodDependencies['named'][$arg];
        }

        // Fall back to component property
        return data_get($this, $arg);
    }

    protected function parseAbilityAndArguments($ability, $arguments): array
    {
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            return [$ability, $arguments];
        }

        return [$this->normalizeGuessedAbilityName($this->method), $ability];
    }
}