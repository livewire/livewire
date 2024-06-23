<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_property_attribute_has_access_to_lifecycle_hooks()
    {
        Livewire::test(new class extends TestComponent {
            #[LifecycleHookAttribute]
            public $count = 0;
        })
        ->assertSetStrict('count', 3);
    }

    public function test_can_set_property_hook_manually()
    {
        Livewire::test(new class extends TestComponent {
            function __construct() {
                $this->setPropertyAttribute('count', new LifecycleHookAttribute);
            }

            public $count = 0;
        })
        ->assertSetStrict('count', 3);
    }

    public function test_can_set_nested_property_hook_manually()
    {
        Livewire::test(new class extends TestComponent {
            function __construct() {
                $this->setPropertyAttribute('items.count', new LifecycleHookAttribute);
            }

            public $items = ['count' => 0];
        })
        ->assertSetStrict('items.count', 3);
    }

    public function test_non_livewire_attribute_are_ignored()
    {
        Livewire::test(new class extends TestComponent {
            #[NonLivewire]
            public $count = 0;
        })
            ->assertSetStrict('count', 0);
    }
}

#[\Attribute]
class LifecycleHookAttribute extends Attribute {
    function mount() { $this->setValue($this->getValue() + 1); }
    function hydrate() { $this->setValue($this->getValue() + 1); }
    function render() { $this->setValue($this->getValue() + 1); }
    function dehydrate() { $this->setValue($this->getValue() + 1); }
}

#[\Attribute]
class NonLivewire {}
