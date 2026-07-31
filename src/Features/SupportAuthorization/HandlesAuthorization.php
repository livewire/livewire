<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    // This method can be called from component action so it should be prepared to act
    // just like regular `$this->authorize()` method as a safety measure
    public function authorizeFromAttribute($ability, $argument = null, $method = null, $parameters = [])
    {
        // Get method name from backtrace if its not provided since we know for sure
        // the method wouldn't be null if its called from attribute
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        if (is_null($argument)) {
            $ability = enum_value($ability);

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
            // Attribute arguments are stored as strings and need to be resolved.
            // Objects passed directly by callers can be used as-is.
            $resolved[] = is_object($arg)
                ? $arg
                : $this->resolveArgument($arg, $method, $resolveMethodDependencies);
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