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
            <div dusk="component">
                @teleport('body')
                    <span>teleportedbar</span>
                @endteleport
            </div>
            HTML; }
        })
            ->assertDontSeeIn('@component', 'teleportedbar')
            ->assertSee('teleportedbar');
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
            <div dusk="component">
                <button dusk="setFoo" type="button" wire:click="setFoo">
                    Set foo
                </button>

                @teleport('body')
                    <span>teleported{{ $foo }}</span>
                @endteleport
            </div>
            HTML; }
        })
            ->assertDontSeeIn('@component', 'teleportedbar')
            ->assertSee('teleportedbar')
            ->waitForLivewire()->click('@setFoo')
            ->assertDontSeeIn('@component', 'teleportedbaz')
            ->assertSee('teleportedbaz');
    }
}
