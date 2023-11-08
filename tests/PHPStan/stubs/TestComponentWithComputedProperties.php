<?php

declare(strict_types=1);

namespace Tests\PHPStan\stubs;

use Livewire\Attributes\Computed;
use Livewire\Component;

final class TestComponentWithComputedProperties extends Component
{
    public function notAComputedProperty(): bool
    {
        return true;
    }

    #[Computed]
    private function privateMethod(): bool
    {
        return true;
    }

    #[Computed]
    protected function protectedMethod(): bool
    {
        return true;
    }

    #[Computed]
    public function property(): bool
    {
        return true;
    }

    /** This is a comment. */
    #[Computed]
    public function propertyWithComments(): bool
    {
        return true;
    }

    /** @deprecated */
    #[Computed]
    public function deprecatedProperty(): bool
    {
        return true;
    }

    /** @deprecated Has a description. */
    #[Computed]
    public function deprecatedPropertyWithDescription(): bool
    {
        return true;
    }

    /** @return array<int,string> */
    #[Computed]
    public function propertyWithGenerics(): array
    {
        return ['foo', 'bar'];
    }
}
