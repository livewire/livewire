<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Attributes\Locked;
use Livewire\Component as BaseComponent;
use Livewire\Form;
use Livewire\Livewire;

class SupportAttributesTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function it_can_updates_form_with_locked_properties()
    {
        Livewire::test(Component::class)
			->set('form.foo', 'bar')
			->assertOk();
    }

}

class SomeForm extends Form {
	#[Locked]
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