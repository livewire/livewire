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
        Livewire::visit(Component::class)
            ->waitForLivewire()
			->set('form.foo', 'bar')
			->assertOk()
			->call('submit')
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

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @return array<string,mixed>
	 */
	protected function rules(): array
	{
		return [
			'id' => 'present|string|nullable',
			'foo' => 'required|string',
		];
	}

}

class Component extends BaseComponent
{
	public SomeForm $form;

	public function mount() {
		$this->form->init('id');
	}

	public function submit() {
		$this->form->validate();
	}

    public function render()
    {
        return <<< 'HTML'
<div>

	<input type='text' wire:model='form.foo' />
	<button wire:click='submit()' />
</div>
HTML;
	}
}