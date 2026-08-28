<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_resetting_required_typed_form_properties_completes_the_next_render()
    {
        Livewire::visit(new class extends Component {
            public BrowserRequiredPostForm $form;

            public string $state = 'initialized';

            public function mount()
            {
                $this->form->title = 'Some Title';
            }

            public function resetForm()
            {
                $this->form->reset();

                $property = new \ReflectionProperty($this->form, 'title');

                $this->state = $property->isInitialized($this->form)
                    ? 'initialized'
                    : 'uninitialized';
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button dusk="reset" wire:click="resetForm">Reset</button>

                        <span dusk="state">{{ $state }}</span>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@state', 'initialized')
            ->click('@reset')
            ->waitForTextIn('@state', 'uninitialized');
    }
}

class BrowserRequiredPostForm extends Form
{
    public string $title;
}
