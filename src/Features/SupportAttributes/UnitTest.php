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

    public function test_component_with_no_attributes_returns_empty_collection_and_uses_cache()
    {
        $component = new class extends TestComponent {
            public $name = 'test';
        };

        $attrs = AttributeCollection::fromComponent($component);
        $this->assertCount(0, $attrs);

        // Repeated call should return empty collection via cached metadata
        $attrs2 = AttributeCollection::fromComponent($component);
        $this->assertCount(0, $attrs2);
    }

    public function test_attributes_inherited_from_parent_class_are_discovered()
    {
        $component = new class extends ParentWithAttributeComponent {};

        $attrs = AttributeCollection::fromComponent($component);
        $this->assertCount(1, $attrs);
        $this->assertInstanceOf(LifecycleHookAttribute::class, $attrs->first());
    }

    public function test_attribute_collection_cache_flush()
    {
        $component = new class extends TestComponent {
            #[LifecycleHookAttribute]
            public $count = 0;
        };

        $attrs = AttributeCollection::fromComponent($component);
        $this->assertCount(1, $attrs);

        AttributeCollection::flushCache();

        $attrsAfterFlush = AttributeCollection::fromComponent($component);
        $this->assertCount(1, $attrsAfterFlush);
    }
}

class ParentWithAttributeComponent extends TestComponent
{
    #[LifecycleHookAttribute]
    public $parentCount = 0;
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
