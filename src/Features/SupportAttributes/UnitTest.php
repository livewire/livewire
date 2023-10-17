<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function property_attribute_has_access_to_lifecycle_hooks()
    {
        Livewire::test(new class extends TestComponent
        {
            #[LifecycleHookAttribute]
            public $count = 0;
        })
            ->assertSet('count', 3);
    }

    /** @test */
    public function can_set_property_hook_manually()
    {
        Livewire::test(new class extends TestComponent
        {
            public function __construct()
            {
                $this->setPropertyAttribute('count', new LifecycleHookAttribute);
            }

            public $count = 0;
        })
            ->assertSet('count', 3);
    }

    /** @test */
    public function can_set_nested_property_hook_manually()
    {
        Livewire::test(new class extends TestComponent
        {
            public function __construct()
            {
                $this->setPropertyAttribute('items.count', new LifecycleHookAttribute);
            }

            public $items = ['count' => 0];
        })
            ->assertSet('items.count', 3);
    }

    /** @test */
    public function non_livewire_attribute_are_ignored()
    {
        Livewire::test(new class extends TestComponent
        {
            #[NonLivewire]
            public $count = 0;
        })
            ->assertSet('count', 0);
    }
}

#[\Attribute]
class LifecycleHookAttribute extends Attribute
{
    public function mount()
    {
        $this->setValue($this->getValue() + 1);
    }

    public function hydrate()
    {
        $this->setValue($this->getValue() + 1);
    }

    public function render()
    {
        $this->setValue($this->getValue() + 1);
    }

    public function dehydrate()
    {
        $this->setValue($this->getValue() + 1);
    }
}

#[\Attribute]
class NonLivewire
{
}
