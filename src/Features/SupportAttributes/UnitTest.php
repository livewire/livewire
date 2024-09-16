<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Js;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Lazy;
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

    public function test_component_has_attribute()
    {
        Livewire::test(NewComponent::class)
                ->assertComponentHasAttribute(Title::class, 'Class Level Attribute')
                ->assertComponentHasAttribute(Validate::class, ['required', 'integer'])
                ->assertComponentHasAttribute(On::class, 'fooEvent');
    }

    public function test_component_has_class_level_attribute_with_value()
    {
        Livewire::test(NewComponent::class)
            ->assertClassHasAttribute(Lazy::class, ['isolate' => false])
            ->assertClassHasAttribute(Title::class, 'Class Level Attribute');
    }

    public function test_component_has_method_level_attribute()
    {
        Livewire::test(NewComponent::class)
            ->assertMethodHasAttribute('jsMethod', Js::class)
            ->assertMethodHasAttribute('doubleFoo', On::class, 'fooEvent')
            ->assertMethodHasAttribute('barMethod', On::class, ['barEvent', 'bazEvent'])
            ->assertMethodHasAttribute('fooToThePowerOfTwo', Computed::class, [
                'persist' => true,
                'seconds' => 7200,
            ])
            ->assertMethodHasAttribute('anotherComputedProperty', Computed::class, [
                'cache' => true,
                'key' => 'homepage-posts',
                'tags' => ['posts', 'homepage'],
            ]);
    }

    public function test_component_has_property_level_attribute()
    {
        Livewire::test(NewComponent::class)
            ->assertPropertyHasAttribute('bar', Session::class, ['key' => 'foo'])
            ->assertPropertyHasAttribute('foo', Validate::class, ['required', 'integer']);
    }

    public function test_component_has_property_with_multiple_attributes()
    {
        Livewire::test(NewComponent::class)
            ->assertPropertyHasAttribute('bar', Validate::class, [
                'rule' => 'required',
                'as' => 'date of birth',
            ])
            ->assertPropertyHasAttribute('baz', Validate::class, [
                'rule' => 'required',
                'message' => 'This is a custom message',
            ])
            ->assertPropertyHasAttribute('baz', Isolate::class);
    }

}

#[Lazy(isolate: false)]
#[Title('Class Level Attribute')]
class NewComponent extends TestComponent {

    #[Validate(['required', 'integer'])]
    public ?int $foo = null;

    #[Validate('required', as: 'date of birth')]
    #[Session(key: 'foo')]
    public string $bar = 'bar';

    #[Validate('required', message: 'This is a custom message')]
    #[Validate('integer', message: 'Why is this not an integer?')]
    #[Isolate]
    public $baz = 'baz';

    #[Js]
    public function jsMethod()
    {
        return <<<'JS'
            $wire.on('fooEvent', () => {
                console.log('fooEvent');
            });
        JS;
    }

    #[On(['barEvent', 'bazEvent'])]
    public function barMethod(): string
    {
        return 'bar';
    }

    #[Computed(persist: true, seconds: 7200)]
    public function fooToThePowerOfTwo(): int
    {
        return $this->foo ** 2;
    }

    #[Computed(cache: true, key: 'homepage-posts', tags: ['posts', 'homepage'])]
    public function anotherComputedProperty(): string
    {
        return 'cached value';
    }

    #[On('fooEvent')]
    public function doubleFoo(): int
    {
        return $this->foo * 2;
    }
}

class HasAllAttributes extends TestComponent {

    #[Validate('required')]
    public $content = '';

    #[Locked]
    public $foo = 'bar';

    #[Computed]
    public function getFoo() {
        return $this->foo;
    }

    #[Reactive]
    public $bar = 'foo';

    #[Modelable]
    public $baz = 'baz';

    #[LifecycleHookAttribute]
    public $count2 = 0;
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
