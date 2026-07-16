<?php

namespace Livewire\Features\SupportAuthorization;

use Attribute;
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
        $this->component->setMethodAndParameters($this->getName(), $parameters);

        wrap($this->component)->handleAuthorization($this->ability, $this->argument);
    }
}