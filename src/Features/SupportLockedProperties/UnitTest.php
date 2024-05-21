<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Livewire;
use Livewire\Component as BaseComponent;
use Livewire\Form;

class UnitTest extends \Tests\TestCase
{
    function test_cant_update_locked_property()
    {
        $this->expectExceptionMessage(
            'Cannot update locked property: [count]'
        );

        Livewire::test(new class extends BaseComponent {
            #[BaseLocked]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSetStrict('count', 1)
        ->set('count', 2);
    }

    function test_cant_deeply_update_locked_property()
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $this->expectExceptionMessage(
            'Cannot update locked property: [foo]'
        );

        Livewire::test(new class extends BaseComponent {
            #[BaseLocked]
            public $foo = ['count' => 1];

            function increment() { $this->foo['count']++; }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSetStrict('foo.count', 1)
        ->set('foo.count', 2);
    }

    function test_can_update_locked_property_with_similar_name()
    {
        Livewire::test(new class extends BaseComponent {
            #[BaseLocked]
            public $count = 1;

            public $count2 = 1;

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSetStrict('count2', 1)
        ->set('count2', 2);
    }

    public function test_it_can_updates_form_with_locked_properties()
    {
        Livewire::test(Component::class)
            ->set('form.foo', 'bar')
            ->assertSetStrict('form.foo', 'bar')
            ->assertOk();
    }
}

class SomeForm extends Form {
    #[BaseLocked]
    public ?string $id = null;
    public string $foo = '';

    public function init(?string $id) {
        $this->id = $id;
    }
}

class Component extends BaseComponent
{
    public SomeForm $form;

    public function mount() {
        $this->form->init('id');
    }

    public function render()
    {
        return <<< 'HTML'
<div>

    <input type='text' wire:model='form.foo' />
</div>
HTML;
    }
}
