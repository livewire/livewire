<?php

namespace Livewire\Features\SupportAuthorization;

use Attribute;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use UnitEnum;

use function Illuminate\Support\enum_value;
use function Livewire\str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseAuthorize extends LivewireAttribute
{
    public function __construct(
        public UnitEnum|string $ability,
        public array|string|null $argument = null,
    ) {}

    public function call(array $parameters) : void
    {
        // Action that does not require a model or class...
        if (is_null($this->argument)) {
            Gate::authorize($this->ability);

            return;
        }

        $arguments = Arr::wrap($this->argument);
        
        // Check if authorization already applied on route level
        $abilityModel = app(PersistentMiddleware::class)->getAuthorizeMiddleware();
        
        if ($abilityModel !== []) {
            [$ability, $model] = $abilityModel;
            
            if (enum_value($this->ability) === $ability && in_array($model, $arguments)) return;
        }

        // Resolve each argument (prioritize method parameters first, then component properties)
        $resolved = [];
        foreach ($arguments as $arg) {
            $resolved[] = $this->resolveArgument($arg, $parameters);
        }

        Gate::authorize($this->ability, $resolved);
    }

    /**
     * Resolve a single argument.
     */
    protected function resolveArgument(string $arg, array $parameters): mixed
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

        if ($methodArgument instanceof \ReflectionParameter && isset($parameters[$arg])) {
            $model = app($methodArgument->getType()->getName())->resolveRouteBinding($parameters[$arg]);

            if (! $model) {
                throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel($methodArgument->getType()->getName());
            }

            return $model;
        }

        // Fall back to component property
        return data_get($this->component, $arg);
    }
}