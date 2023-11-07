<?php

namespace Livewire\Features\SupportStaticPartials;

use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_mark_a_partial_as_static_and_only_send_over_the_wire_once()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    @static
                    <h2 dusk="static">{{ $count }}</h2>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@static', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@static', '1')
        ;
    }

    /** @test */
    public function statics_can_be_conditionally_added()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    @if ($count > 1)
                    @static
                    <h2 dusk="static">{{ $count }}</h2>
                    @endstatic
                    @endif
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertMissing('@static')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@static', '2')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '3')
        ->assertSeeIn('@static', '2')
        ;
    }

    /** @test */
    public function statics_can_be_nested()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    @static
                    <div>
                        <h2 dusk="nested-static-1">{{ $count }}</h2>

                        @static
                            <h2 dusk="nested-static-2">{{ $count }}</h2>
                        @endstatic
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ;
    }

    /** @test */
    public function statics_can_have_slots()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    @static
                    <div>
                        <h2 dusk="nested-static-1">{{ $count }}</h2>

                        @staticSlot
                            <h2 dusk="nested-static-2">{{ $count }}</h2>
                        @endstaticSlot
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '2')
        ;
    }

    /** @test */
    public function statics_can_be_nested_inside_other_statics_slots()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    @static
                    <div>
                        <h2 dusk="nested-static-1">{{ $count }}</h2>

                        @staticSlot
                            @static
                            <div>
                                <h2 dusk="nested-static-2">{{ $count }}</h2>

                                @staticSlot
                                    <h2 dusk="nested-static-3">{{ $count }}</h2>
                                @endstaticSlot

                                <h2 dusk="nested-static-4">{{ $count }}</h2>

                                @staticSlot
                                    <h2 dusk="nested-static-5">{{ $count }}</h2>
                                @endstaticSlot
                            </div>
                            @endstatic
                        @endstaticSlot
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ->assertSeeIn('@nested-static-3', '1')
        ->assertSeeIn('@nested-static-4', '1')
        ->assertSeeIn('@nested-static-5', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ->assertSeeIn('@nested-static-3', '2')
        ->assertSeeIn('@nested-static-4', '1')
        ->assertSeeIn('@nested-static-5', '2')
        ;
    }

    /** @test */
    public function statics_can_be_used_inside_blade_components()
    {
        Blade::anonymousComponentPath(__DIR__);

        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    <x-test :$count />
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '1')
        ->assertSeeIn('@nested-static-3', '1')
        ->assertSeeIn('@nested-static-4', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@nested-static-1', '1')
        ->assertSeeIn('@nested-static-2', '2')
        ->assertSeeIn('@nested-static-3', '1')
        ->assertSeeIn('@nested-static-4', '2')
        ;
    }

    /** @test */
    public function blade_components_can_be_repeated()
    {
        Blade::anonymousComponentPath(__DIR__);

        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    <div dusk="nested-component-1">
                        <x-test :$count />
                    </div>

                    <div dusk="nested-component-2">
                        <x-test :$count />
                    </div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('h1', '1')
        ->assertSeeIn('@nested-component-1 h2', '1')
        ->assertSeeIn('@nested-component-1 h3', '1')
        ->assertSeeIn('@nested-component-1 h4', '1')
        ->assertSeeIn('@nested-component-1 h5', '1')
        ->assertSeeIn('@nested-component-2 h2', '1')
        ->assertSeeIn('@nested-component-2 h3', '1')
        ->assertSeeIn('@nested-component-2 h4', '1')
        ->assertSeeIn('@nested-component-2 h5', '1')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('h1', '2')
        ->assertSeeIn('@nested-component-1 h2', '1')
        ->assertSeeIn('@nested-component-1 h3', '2')
        ->assertSeeIn('@nested-component-1 h4', '1')
        ->assertSeeIn('@nested-component-1 h5', '2')
        ->assertSeeIn('@nested-component-2 h2', '1')
        ->assertSeeIn('@nested-component-2 h3', '2')
        ->assertSeeIn('@nested-component-2 h4', '1')
        ->assertSeeIn('@nested-component-2 h5', '2')
        ;
    }

    /** @test */
    public function blade_components_can_be_repeated_with_different_initial_static_content()
    {
        Blade::anonymousComponentPath(__DIR__);

        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="dynamic">{{ $count }}</h1>

                    <div dusk="nested-component-1">
                        <x-test :count="$count" />
                    </div>

                    <div dusk="nested-component-2">
                        <x-test :count="$count + 1" />
                    </div>
                </div>
                HTML;
            }
        })
        ->tinker()
        ->assertSeeIn('h1', '1')
        ->assertSeeIn('@nested-component-1 h2', '1')
        ->assertSeeIn('@nested-component-1 h3', '1')
        ->assertSeeIn('@nested-component-1 h4', '1')
        ->assertSeeIn('@nested-component-1 h5', '1')
        ->assertSeeIn('@nested-component-2 h2', '2')
        ->assertSeeIn('@nested-component-2 h3', '2')
        ->assertSeeIn('@nested-component-2 h4', '2')
        ->assertSeeIn('@nested-component-2 h5', '2')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('h1', '2')
        ->assertSeeIn('@nested-component-1 h2', '1')
        ->assertSeeIn('@nested-component-1 h3', '2')
        ->assertSeeIn('@nested-component-1 h4', '1')
        ->assertSeeIn('@nested-component-1 h5', '2')
        ->assertSeeIn('@nested-component-2 h2', '2')
        ->assertSeeIn('@nested-component-2 h3', '3')
        ->assertSeeIn('@nested-component-2 h4', '2')
        ->assertSeeIn('@nested-component-2 h5', '3')
        ;
    }
}
