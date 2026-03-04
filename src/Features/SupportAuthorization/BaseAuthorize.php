<?php

namespace Livewire\Features\SupportAuthorization;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use UnitEnum;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseAuthorize extends LivewireAttribute
{
    public function __construct(
        public UnitEnum|string $ability,
        public string|null $argument = null,
    ) {}

    public function call(array $parameters) : void
    {
        // Action that does not require a model or class...
        if (is_null($this->argument))
        {
            Gate::authorize($this->ability);

            return;
        }

        // Action that does not require a model, for example a 'create' action...
        if (is_string($this->argument) && class_exists($this->argument))
        {
            Gate::authorize($this->ability, $this->argument);

            return;
        }

        // Action that requires a model, extracted from the component...
        if (is_string($this->argument) && $model = data_get($this->component, $this->argument))
        {
            Gate::authorize($this->ability, $model);

            return;
        }

        $methodArgument = Arr::first(
            (new \ReflectionObject($this->component))->getMethod($this->getName())->getParameters(),
            fn (\ReflectionParameter $parameter) : bool => $parameter->getName() === $this->argument,
        );

        // Action that requires a model, extracted from the called method...
        Gate::authorize($this->ability, app($methodArgument->getType()->getName())->resolveRouteBinding($parameters[$this->argument]));
    }
}
