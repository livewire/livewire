<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Tests\Unit\TestCase;

class PublicEnumHydrationHooksTest extends TestCase
{
    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicEnumCasters::class)
            ->call('storeTypeOf')
            ->assertSet('typeOf', TestEnum::class)
            ->assertSet('enum', TestEnum::from('Be excellent to each other'));
    }
}

class ComponentWithPublicEnumCasters extends Component
{
    public $typeOf;
    public $enum;

    public function hydrate()
    {
        $this->enum = TestEnum::TEST;
    }

    public function dehydrate()
    {
        $this->enum = TestEnum::from($this->enum->value);
    }

    public function mount()
    {
        $this->enum = TestEnum::TEST;
    }

    public function storeTypeOf()
    {
        $this->typeOf = get_class($this->enum);
    }

    public function render()
    {
        return view('null-view');
    }
}
