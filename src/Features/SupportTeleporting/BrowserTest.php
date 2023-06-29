<?php

namespace Livewire\Features\SupportTeleporting;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_teleport_dom_via_blade_directive()
    {
        Livewire::visit(new class extends Component {
            public function render() { return <<<'HTML'
            <div>
                <div>
                    <span id="foo"></span>
                </div>

                @teleport('#foo')
                    <span>bar</span>
                @endteleport
            </div>
            HTML; }
        })->assertSeeIn('#foo', 'bar');
    }

    /** @test */
    public function can_teleport_dom_via_blade_directive_then_change_it()
    {
        Livewire::visit(new class extends Component {
            public $foo = 'bar';

            public function setFoo()
            {
                $this->foo = 'baz';
            }

            public function render() { return <<<'HTML'
            <div>
                <div>
                    <span id="foo"></span>
                </div>

                <button dusk="setFoo" type="button" wire:click="setFoo">
                    Set foo
                </button>

                @teleport('#foo')
                    <span>{{ $foo }}</span>
                @endteleport
            </div>
            HTML; }
        })->assertSeeIn('#foo', 'bar')->click('@setFoo')->assertSeeIn('#foo', 'baz');
    }
}
