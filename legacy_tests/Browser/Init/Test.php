<?php

namespace LegacyTests\Browser\Init;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_init(): void
    {
        Livewire::visit(new class extends Component {
            public $output = '';

            public function setOutputToFoo()
            {
                $this->output = 'foo';
            }

            public function render()
            {
                return <<<'HTML'
                    <div wire:init="setOutputToFoo">
                        <span dusk="output">{{ $output }}</span>
                    </div>
                HTML;
            }
        })
            /**
             * wire:init runs on page load.
             */
            ->waitForText('foo')
            ->assertSee('foo')
        ;
    }
}
