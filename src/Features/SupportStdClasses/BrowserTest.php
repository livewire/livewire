<?php

namespace Livewire\Features\SupportStdClasses;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    function can_use_wire_stdclass_property()
    {
        Livewire::visit(new class extends Component {
            public $obj;

            function mount()
            {
                $this->obj = (object)[];
            }

            function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="text" dusk="input" wire:model.live="obj.property" />
                        <span dusk="output">{{ $obj?->property ?? '' }}</span>
                    </div>
                HTML;
            }
        })
            ->type('@input', 'foo')
            ->pause(300)
            ->append('@input', 'bar')
            ->pause(300)
            ->assertSeeIn('@output', 'foobar')
        ;
    }
}
