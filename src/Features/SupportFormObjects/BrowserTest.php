<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_form_object_property_is_not_overwritten_by_concurrent_server_update_to_sibling_property()
    {
        Livewire::visit(new class extends Component {
            public FormWithTwoFieldsStub $form;

            public function slowAction()
            {
                usleep(500 * 1000); // 500ms

                $this->form->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" dusk="message" wire:model="form.message">

                    <button dusk="slow" wire:click="slowAction">Slow</button>

                    <span dusk="count">{{ $form->count }}</span>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        // Trigger the slow server action...
        ->click('@slow')
        // While the request is in-flight, type into the message field...
        ->pause(100)
        ->type('@message', 'typed during request')
        // Wait for the slow action to complete...
        ->waitForTextIn('@count', '1')
        // The message field should still have what the user typed...
        ->assertInputValue('@message', 'typed during request')
        ;
    }
}

class FormWithTwoFieldsStub extends Form
{
    public string $message = '';
    public int $count = 0;
}
