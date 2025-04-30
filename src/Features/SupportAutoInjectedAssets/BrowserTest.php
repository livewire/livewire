<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_livewire_styles_take_preference_over_other_styles()
    {
        Livewire::visit(new class extends Component {
            #[Layout('layouts.app-with-styles')]
            function render()
            {

                return <<<'HTML'
                <div>
                    <div wire:loading class="show" dusk="loading">Loading</div>

                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
        ->assertNotVisible('@loading')
        ->waitForLivewire()->click('@refresh')
        ->assertNotVisible('@loading')
        ;
    }
}
