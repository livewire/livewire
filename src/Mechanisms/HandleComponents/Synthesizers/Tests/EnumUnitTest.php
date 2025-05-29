<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Illuminate\Validation\Rule;
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

    public function test_an_enum_can_be_validated()
    {
        Livewire::test(ComponentWithValidatedEnum::class)
            ->call('save')
            ->assertHasErrors('enum')
            ->set('enum', ValidatedEnum::TEST->value)
            ->call('save')
            ->assertHasNoErrors();
    }
}

enum TestingEnum: string
{
    case TEST = 'Be excellent to each other';
}

enum ValidatedEnum: string
{
    case TEST = 'test';
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

class ComponentWithValidatedEnum extends TestComponent
{
    public ValidatedEnum $enum;

    public function rules()
    {
        return [
            'enum' => ['required', Rule::enum(ValidatedEnum::class)],
        ];
    }

    public function save()
    {
        $validatedData = $this->validate();
        // Check that the validated enum is still an Enum value
        if (!($this->enum instanceof ValidatedEnum)) {
            throw new \Exception('The type of Enum has been changed.');
        }
    }
}
