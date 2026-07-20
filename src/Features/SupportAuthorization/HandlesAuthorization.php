<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    protected ?string $method = null;

    public function setAuthorizationMethod($method = null): void
    {
        $this->method = $method;
    }

    public function resolveArgument(array $arguments, array $parameters): mixed
    {
        if (is_null($this->method)) return null;

        // Resolve method dependencies lazily, then reuse them for multi-argument authorization checks...
        $methodDependencies = null;
        $resolveMethodDependencies = function () use (&$methodDependencies, $parameters): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this, $this->method],
                $parameters,
            );
        };

        // Resolve each argument (prioritize method parameters first, then component properties)
        $resolved = [];
        foreach ($arguments as $arg) {
            // Action that does not require a model, for example a 'create' action...
            if (class_exists($arg)) {
                $resolved[] = $arg;

                continue;
            }

            // Try method parameter first (prioritized per rules)
            $methodArgument = Arr::first(
                (new \ReflectionObject($this))->getMethod($this->method)->getParameters(),
                fn (\ReflectionParameter $parameter): bool => $parameter->getName() === $arg,
            );

            if ($methodArgument instanceof \ReflectionParameter) {
                $methodDependencies = $resolveMethodDependencies();

                $resolved[] = $methodDependencies['named'][$arg];

                continue;
            }

            // Fall back to component property
            $resolved[] = data_get($this, $arg);
        }

        return $resolved;
    }

    protected function parseAbilityAndArguments($ability, $arguments): array
    {
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            // Need to reset the property in case attribute is used along with `$this->authorize()`
            $this->setAuthorizationMethod();

            return [$ability, $arguments];
        }

        // Because this method override the original method,
        // we need to make sure it gets the right method name
        // if its called from `$this->authorize()` inside component action
        $method = $this->method ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

        // Need to reset the property in case attribute is used along with `$this->authorize()`
        $this->setAuthorizationMethod();

        return [$this->normalizeGuessedAbilityName($method), $ability];
    }
}