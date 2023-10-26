<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class EnumUnitTest extends \Tests\TestCase
{
    #[Test]
    public function public_properties_can_be_cast()
    {
        Livewire::test(ComponentWithPublicEnumCasters::class)
            ->call('storeTypeOf')
            ->assertSet('typeOf', TestingEnum::class)
            ->assertSet('enum', TestingEnum::from('Be excellent to each other'));
    }
}

enum TestingEnum: string
{
    case TEST = 'Be excellent to each other';
}

class ComponentWithPublicEnumCasters extends Component
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

    public function render()
    {
        return view('null-view');
    }
}
