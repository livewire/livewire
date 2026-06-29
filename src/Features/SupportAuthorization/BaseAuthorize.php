<?php

namespace Livewire\Features\SupportAuthorization;

use Attribute;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\ImplicitlyBoundMethod;
use UnitEnum;

use function Illuminate\Support\enum_value;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseAuthorize extends LivewireAttribute
{
    use AuthorizesRequests;

    public function __construct(
        public UnitEnum|string $ability,
        public array|string|null $argument = null,
    ) {}

    public function call(array $parameters) : void
    {
        // Action that does not require a model or class...
        if (is_null($this->argument)) {
            $this->authorize($this->ability);

            return;
        }

        $arguments = Arr::wrap($this->argument);

        // Resolve method dependencies lazily, then reuse them for multi-argument authorization checks...
        $methodDependencies = null;
        $resolveMethodDependencies = function () use (&$methodDependencies, $parameters): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this->component, $this->getName()],
                $parameters,
            );
        };

        // Resolve each argument (prioritize method parameters first, then component properties)
        $resolved = [];
        foreach ($arguments as $arg) {
            $resolved[] = $this->resolveArgument($arg, $parameters, $resolveMethodDependencies);
        }

        $this->authorize($this->ability, $resolved);
    }

    /**
     * Resolve a single argument.
     */
    protected function resolveArgument(string $arg, array $parameters, \Closure $resolveMethodDependencies): mixed
    {
        // Action that does not require a model, for example a 'create' action...
        if (class_exists($arg)) {
            return $arg;
        }

        // Try method parameter first (prioritized per rules)
        $methodArgument = Arr::first(
            (new \ReflectionObject($this->component))->getMethod($this->getName())->getParameters(),
            fn (\ReflectionParameter $parameter) : bool => $parameter->getName() === $arg,
        );

        if ($methodArgument instanceof \ReflectionParameter) {
            $methodDependencies = $resolveMethodDependencies();

            return $methodDependencies['named'][$arg];
        }

        // Fall back to component property
        return data_get($this->component, $arg);
    }

    protected function parseAbilityAndArguments($ability, $arguments)
    {
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            return [$ability, $arguments];
        }

        return [$this->normalizeGuessedAbilityName($this->getName()), $ability];
    }
}
