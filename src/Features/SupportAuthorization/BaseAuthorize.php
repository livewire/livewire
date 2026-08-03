<?php

namespace Livewire\Features\SupportAuthorization;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\wrap;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class BaseAuthorize extends LivewireAttribute
{
    public function __construct(
        public \UnitEnum|string $ability,
        public array|string|null $argument = null,
    ) {}

    public function call(array $parameters) : void
    {
        wrap($this->component)->authorizeFromAttribute(
            $this->ability,
            $this->argument,
            $this->getName(),
            $parameters
        );
    }
}
