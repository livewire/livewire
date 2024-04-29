<?php

namespace LegacyTests\Browser\ProductionTest;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_ensure_livewire_runs_when_app_debug_is_set_to_false(): void
    {
        Livewire::visit(new class extends Component {
            public $foo = 'squishy';

            public function mount()
            {
                config()->set('app.debug', false);
            }

            public function render()
            {
                return <<< 'HTML'
                    <div>
                        <input type="text" wire:model="foo" dusk="foo">
                    </div>
                HTML;
            }
        })
            /**
             * Just need to check input is filled to ensure Livewire has started properly.
             * Have set app.debug to false inside mount method in component
             */
            ->assertInputValue('@foo', 'squishy')
        ;
    }
}
