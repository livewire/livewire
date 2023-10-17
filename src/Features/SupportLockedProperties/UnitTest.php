<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Component as BaseComponent;
use Livewire\Form;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function cant_update_locked_property()
    {
        $this->expectExceptionMessage(
            'Cannot update locked property: [count]'
        );

        Livewire::test(new class extends BaseComponent
        {
            #[BaseLocked]
            public $count = 1;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('count', 1)
            ->set('count', 2);
    }

    /** @test */
    public function cant_deeply_update_locked_property()
    {
        $this->expectExceptionMessage(
            'Cannot update locked property: [foo]'
        );

        Livewire::test(new class extends BaseComponent
        {
            #[BaseLocked]
            public $foo = ['count' => 1];

            public function increment()
            {
                $this->foo['count']++;
            }

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('foo.count', 1)
            ->set('foo.count', 2);
    }

    /** @test */
    public function can_update_locked_property_with_similar_name()
    {
        Livewire::test(new class extends BaseComponent
        {
            #[BaseLocked]
            public $count = 1;

            public $count2 = 1;

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('count2', 1)
            ->set('count2', 2);
    }

    /** @test */
    public function it_can_updates_form_with_locked_properties()
    {
        Livewire::test(Component::class)
            ->set('form.foo', 'bar')
            ->assertSet('form.foo', 'bar')
            ->assertOk();
    }
}

class SomeForm extends Form
{
    #[BaseLocked]
    public ?string $id = null;

    public string $foo = '';

    public function init(?string $id)
    {
        $this->id = $id;
    }
}

class Component extends BaseComponent
{
    public SomeForm $form;

    public function mount()
    {
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
