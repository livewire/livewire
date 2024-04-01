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

    /** @test */
    public function morphdom_doesnt_remove_subsequent_teleports_if_there_are_multiple()
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

    /** @test */
    public function can_handle_xfor_and_xif_state_when_request_happened()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<<'HTML'
                    <div>
                        <template x-teleport="body">
                            <div x-data="{ open: false }" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                <button type="button" wire:click="$refresh" @click="open = !open" dusk="button">
                                    Refresh
                                </button>

                                <!-- tinkering `open` state... -->
                                <div x-text="'open value: ' + open"></div>
                                <div x-show="open">x-show open</div>

                                <div>
                                    <template x-if="open">
                                        <div>x-if open</div>
                                    </template>
                                </div>

                                <div>
                                    <template x-for="item in 3" :key="item">
                                        <span x-text="item"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                HTML;
            }
        })
            ->waitForLivewire()->click('@button')
            ->assertSee('x-if open')
            ->assertDontSee('123123')
            ->waitForLivewire()->click('@button')
            ->assertDontSee('123123')
            ->assertDontSee('x-if open');
    }
}
