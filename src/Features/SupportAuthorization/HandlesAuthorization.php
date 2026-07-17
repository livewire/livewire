<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests {
        authorize as private baseAuthorize;
    }

    protected ?string $method = null;
    protected ?array $parameters = null;

    public function setMethodAndParameters($method, $parameters): void
    {
        $this->method = $method;
        $this->parameters = $parameters;
    }

    /**
     * Authorize a given action for the current user.
     *
     * @param  mixed  $ability
     * @param  mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        if (is_null($this->method) || is_null($this->parameters)) {
            return $this->baseAuthorize($ability, $arguments);
        }

        // Action that does not require a model or class...
        if (is_null($arguments)) {
            return $this->baseAuthorize($ability);
        }

        $arguments = Arr::wrap($arguments);

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

        return $this->baseAuthorize($ability, $resolved);
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

        // Because this method override the original method,
        // we need to make sure it gets the right method name
        // if its called from `$this->authorize()` inside component action where the 4 stacks comes from
        // [3]action -> [2]authorize() -> [1]baseAuthorize() -> [0]parseAbilityAndArguments
        $this->method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4)[3]['function'];

        return [$this->normalizeGuessedAbilityName($this->method), $ability];
    }
}