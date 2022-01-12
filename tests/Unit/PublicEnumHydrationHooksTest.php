<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Tests\Unit\TestCase;

class PublicEnumHydrationHooksTest extends TestCase
{
    /**
     * @test
     * @requires PHP 8.1
     */
    public function public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicEnumCasters::class)
            ->call('storeTypeOf')
            ->assertSet('typeOf', Browser\SupportEnums\TestEnum::class)
            ->assertSet('enum', Browser\SupportEnums\TestEnum::from('Be excellent to each other'));
    }
}

class ComponentWithPublicEnumCasters extends Component
{
    public $typeOf;
    public $enum;

    public function hydrate()
    {
        $this->enum = Browser\SupportEnums\TestEnum::TEST;
    }

    public function dehydrate()
    {
        $this->enum = Browser\SupportEnums\TestEnum::from($this->enum->value);
    }

    public function mount()
    {
        $this->enum = Browser\SupportEnums\TestEnum::TEST;
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
