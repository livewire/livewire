<?php

namespace Livewire\Features\SupportValidation;

use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function validation_attribute_rules_triggered_on_save()
    {
        Livewire::visit(new class extends Component
        {
            #[Rule(['required', 'min:3'])]
            public string $title = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model="title" dusk="title"/>
                    <div dusk="error">@error('title') {{ $message }} @enderror</div>

                    <button wire:click="save" dusk="save">Save</button>
                </div>
                HTML;
            }

            public function save()
            {
            }
        })
            ->type('@title', 'a')
            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@error', 'The title field must be at least 3 characters.');
    }
}
