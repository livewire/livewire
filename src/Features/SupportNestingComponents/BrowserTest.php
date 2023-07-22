<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function nested_components_do_not_error_with_empty_elements_on_page()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>
                        </div>

                        <button type="button" wire:click="$refresh" dusk="refresh">
                            Refresh
                        </button>

                        <livewire:child />

                        <div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child">
                        Child
                    </div>
                    HTML;
                }
            }
        ])
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ;
    }
}
