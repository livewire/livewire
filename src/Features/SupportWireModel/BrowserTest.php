<?php

namespace Livewire\Features\SupportWireModel;

use Livewire\Component;
use Livewire\Features\SupportValidation\Rule;
use Livewire\Livewire;
use Tests\TestComponent;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_update_wire_model_property_from_component()
    {
        Livewire::visit(new class extends Component {
            public string $value = 'old';

            public function changeValue(): void
            {
                $this->value = 'newvalue';
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        {{$value}}

                        <br>
                        <input dusk="input" type="text" wire:model.blur="value" />
                        <br>

                        <a href="#" wire:click="changeValue" dusk="change">Change input value to "new" on server</a>
                    </div>
                HTML;
            }
        })
            ->waitForLivewire()
            ->type('@input', 'some random value')
            ->click('@change')
            ->assertInputValue('@input', 'newvalue');
    }
}
