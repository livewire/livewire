<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    protected $authorizeMethod = null;

    public function authorizeFromAttribute($method, $ability, $argument, $parameters)
    {
        $this->authorizeMethod = $method;

        if (is_null($argument)) {
            return $this->authorize($ability);
        }

        // Resolve method dependencies lazily, then reuse them for multi-argument authorization checks...
        $methodDependencies = null;
        $resolveMethodDependencies = function () use (&$methodDependencies, $parameters): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this, $this->authorizeMethod],
                $parameters,
            );
        };

        $resolved = [];
        foreach (Arr::wrap($argument) as $arg) {
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
            (new \ReflectionObject($this))->getMethod($this->authorizeMethod)->getParameters(),
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
        // if its called from `$this->authorize()` inside component action
        $method = $this->authorizeMethod ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

        return [$this->normalizeGuessedAbilityName($method), $ability];
    }
}