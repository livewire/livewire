<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Livewire;
use Tests\TestComponent;
use ValueError;

class EnumUnitTest extends \Tests\TestCase
{
    public function test_public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicEnumCasters::class)
            ->call('storeTypeOf')
            ->assertSetStrict('typeOf', TestingEnum::class)
            ->assertSetStrict('enum', TestingEnum::from('Be excellent to each other'));
    }

    public function test_nullable_public_property_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicEnumCaster::class)
            ->assertSetStrict('status', null)
            ->updateProperty('status', 'Be excellent to each other')
            ->assertSetStrict('status', TestingEnum::TEST)
            ->updateProperty('status', '')
            ->assertSetStrict('status', null);

        $this->expectException(ValueError::class);
        $testable->updateProperty('status', 'Be excellent excellent to each other');
    }
}

enum TestingEnum: string
{
    case TEST = 'Be excellent to each other';
}

class ComponentWithPublicEnumCasters extends TestComponent
{
    public $typeOf;
    public $enum;

    public function hydrate()
    {
        $this->enum = TestingEnum::TEST;
    }

    public function dehydrate()
    {
        $this->enum = TestingEnum::from($this->enum->value);
    }

    public function mount()
    {
        $this->enum = TestingEnum::TEST;
    }

    public function storeTypeOf()
    {
        $this->typeOf = get_class($this->enum);
    }
}

class ComponentWithNullablePublicEnumCaster extends TestComponent
{
    public ?TestingEnum $status = null;
}
