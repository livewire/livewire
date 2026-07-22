<?php

namespace Livewire\Features\SupportAuthorization;

use Attribute;
use Illuminate\Support\Arr;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use UnitEnum;

use function Livewire\wrap;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseAuthorize extends LivewireAttribute
{
    public function __construct(
        public UnitEnum|string $ability,
        public array|string|null $argument = null,
    ) {}

    public function call(array $parameters)
    {
        $wrapper = wrap($this->component);

        $wrapper->setAuthorizationMethod($this->getName());

        if (is_null($this->argument)) {
            $wrapper->authorize($this->ability);

            return;
        }

        $resolved = $wrapper->resolveArgument(Arr::wrap($this->argument), $parameters);

        $wrapper->authorize($this->ability, $resolved);
    }
}