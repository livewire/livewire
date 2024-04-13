<?php

namespace Livewire\Features\SupportStdClasses;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\BrowserTestCase;

class UnitTest extends BrowserTestCase
{
    #[Test]
    function can_use_wire_stdclass_property()
    {
        Livewire::test(new class extends Component {
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
            ->assertSet('obj.property', null)
            ->set('obj.property', 'foo')
            ->assertSet('obj.property', 'foo')
            ->set('obj.property', 'bar')
            ->assertSet('obj.property', 'bar')
        ;
    }
}
