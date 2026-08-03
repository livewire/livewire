<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    public function authorizeFromAttribute($ability, $argument = null, $method = null, $parameters = [])
    {
        // Safety measure if this method get called directly inside component action
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        if (is_null($argument)) {
            $ability = enum_value($ability);

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

        // Resolve each argument (prioritize method parameters first, then component properties)
        $resolved = [];
        foreach (Arr::wrap($argument) as $arg) {
            $resolved[] = $this->resolveArgument($arg, $method, $resolveMethodDependencies);
        }

        $this->authorize($ability, $resolved);
    }

    protected function resolveArgument(string|object $arg, string $method, \Closure $resolveMethodDependencies): mixed
    {
        // Action that does not require a model, for example a 'create' action...
        if (is_object($arg) || (is_string($arg) && class_exists($arg))) {
            return $arg;
        }

        // Try method parameter first (prioritized per rules)
        $methodArgument = Arr::first(
            (new \ReflectionObject($this))->getMethod($method)->getParameters(),
            fn (\ReflectionParameter $parameter) : bool => $parameter->getName() === $arg,
        );

        if ($methodArgument instanceof \ReflectionParameter) {
            $methodDependencies = $resolveMethodDependencies();

            return $methodDependencies['named'][$arg];
        }

        // Fall back to component property
        return data_get($this, $arg);
    }
}