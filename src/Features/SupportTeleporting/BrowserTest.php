<?php

namespace Livewire\Features\SupportTeleporting;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_can_teleport_dom_via_blade_directive()
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

    public function test_can_teleport_dom_via_blade_directive_then_change_it()
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

    public function test_morphdom_doesnt_remove_subsequent_teleports_if_there_are_multiple()
    {
        Livewire::visit(new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div dusk="component">
                <button wire:click="$set('count', 2)" dusk="button">refresh</button>
                <div>
                    <template x-teleport="body">
                        <span>first teleport. run ({{ $count }})</span>
                    </template>

                    <span dusk="first-check">{{ $count }}</span>
                </div>
                <div>
                    <template x-teleport="body">
                        <span>second teleport. run ({{ $count }})</span>
                    </template>

                    <span dusk="second-check">{{ $count }}</span>
                </div>
            </div>
            HTML; }
        })
            ->assertSee('first teleport')
            ->assertSee('second teleport')
            ->assertSeeIn('@first-check', '1')
            ->assertSeeIn('@second-check', '1')
            ->waitForLivewire()->click('@button')
            ->assertSee('first teleport')
            ->assertSee('second teleport')
            ->assertSeeIn('@first-check', '2')
            ->assertSeeIn('@second-check', '2');
    }

    public function test_conditionally_rendered_elements_initialise_in_teleport()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public $output = 'start';

            public function thing1(){
                $this->output .= 'thing1';
            }

            public function thing2(){
                $this->output .= 'thing2';
            }

            public function render() { return <<<'HTML'
            <div>
                <button wire:click="$toggle('show')" dusk="show">Show</button>

                <p dusk="output">{{ $output }}</p>

                @teleport('body')
                <div>
                    <button wire:click="thing1" dusk="thing1">Thing 1</button>

                    @if ($show)
                        <button wire:click="thing2" dusk="thing2">Thing 2</button>
                    @endif
                </div>
                @endteleport
            </div>
            HTML; }
        })
            ->assertSeeIn('@output', 'start')
            ->assertDontSeeIn('@output', 'thing1')
            ->assertDontSeeIn('@output', 'thing2')
            
            ->waitForLivewire()->click('@thing1')
            ->assertSeeIn('@output', 'startthing1')
            ->assertDontSeeIn('@output', 'thing2')

            ->waitForLivewire()->click('@show')
            ->assertSeeIn('@output', 'startthing1')
            ->assertDontSeeIn('@output', 'thing2')

            ->waitForLivewire()->click('@thing2')
            ->assertSeeIn('@output', 'startthing1thing2');

    }
}
