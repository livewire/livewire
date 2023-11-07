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
                    <h2 dusk="static">foo</h2>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@static', 'foo')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@static', 'foo')
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
                    <h2 dusk="static">foo</h2>
                    @endstatic
                    @endif
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@dynamic', '1')
        ->assertMissing('@static')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@static', 'foo')
        ->tap($this->assertFetchedHtmlContains('foo'))
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@dynamic', '3')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->assertSeeIn('@static', 'foo')
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
                        <h2 dusk="nested-static-1">foo</h2>

                        @static
                            <h2 dusk="nested-static-2">bar</h2>
                        @endstatic
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@dynamic', '1')
        ->assertSeeIn('@nested-static-1', 'foo')
        ->assertSeeIn('@nested-static-2', 'bar')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->tap($this->assertFetchedHtmlIsMissing('bar'))
        ->assertSeeIn('@dynamic', '2')
        ->assertSeeIn('@nested-static-1', 'foo')
        ->assertSeeIn('@nested-static-2', 'bar')
        ;
    }

    /** @test */
    public function statics_can_have_dynamic_slots()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() {
                $this->count++;

                return <<<'HTML'
                <div>
                    <button dusk="button" wire:click="$refresh">refresh</button>

                    <h1 dusk="raw">{{ $count }}</h1>

                    @static
                    <div>
                        <h2 dusk="static">foo</h2>

                        @dynamic
                            <h2 dusk="dynamic">{{ $count }}</h2>
                        @enddynamic
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@raw', '1')
        ->assertSeeIn('@static', 'foo')
        ->assertSeeIn('@dynamic', '1')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->assertSeeIn('@raw', '2')
        ->assertSeeIn('@static', 'foo')
        ->assertSeeIn('@dynamic', '2')
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

                    <h1 dusk="raw">{{ $count }}</h1>

                    @static
                    <div>
                        <h2 dusk="static-1">foo</h2>

                        @dynamic
                            @static
                            <div>
                                <h2 dusk="static-2">bar</h2>

                                @dynamic
                                    <h2 dusk="dynamic-1">{{ $count }}</h2>
                                @enddynamic

                                <h2 dusk="static-3">baz</h2>

                                @dynamic
                                    <h2 dusk="dynamic-2">{{ $count }}</h2>
                                @enddynamic
                            </div>
                            @endstatic
                        @enddynamic
                    </div>
                    @endstatic
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@raw', '1')
        ->assertSeeIn('@static-1', 'foo')
        ->assertSeeIn('@static-2', 'bar')
        ->assertSeeIn('@dynamic-1', '1')
        ->assertSeeIn('@static-3', 'baz')
        ->assertSeeIn('@dynamic-2', '1')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->tap($this->assertFetchedHtmlIsMissing('bar'))
        ->tap($this->assertFetchedHtmlIsMissing('baz'))
        ->assertSeeIn('@raw', '2')
        ->assertSeeIn('@static-1', 'foo')
        ->assertSeeIn('@static-2', 'bar')
        ->assertSeeIn('@dynamic-1', '2')
        ->assertSeeIn('@static-3', 'baz')
        ->assertSeeIn('@dynamic-2', '2')
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

                    <h1 dusk="raw">{{ $count }}</h1>

                    <x-test :$count />
                </div>
                HTML;
            }
        })
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('@raw', '1')
        ->assertSeeIn('@static-1', 'foo')
        ->assertSeeIn('@static-2', 'bar')
        ->assertSeeIn('@dynamic-1', '1')
        ->assertSeeIn('@dynamic-2', '1')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->tap($this->assertFetchedHtmlIsMissing('bar'))
        ->assertSeeIn('@raw', '2')
        ->assertSeeIn('@static-1', 'foo')
        ->assertSeeIn('@static-2', 'bar')
        ->assertSeeIn('@dynamic-1', '2')
        ->assertSeeIn('@dynamic-2', '2')
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
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('h1', '1')
        ->assertSeeIn('@nested-component-1 h2', 'foo')
        ->assertSeeIn('@nested-component-1 h3', '1')
        ->assertSeeIn('@nested-component-1 h4', 'bar')
        ->assertSeeIn('@nested-component-1 h5', '1')
        ->assertSeeIn('@nested-component-2 h2', 'foo')
        ->assertSeeIn('@nested-component-2 h3', '1')
        ->assertSeeIn('@nested-component-2 h4', 'bar')
        ->assertSeeIn('@nested-component-2 h5', '1')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->tap($this->assertFetchedHtmlIsMissing('bar'))
        ->assertSeeIn('h1', '2')
        ->assertSeeIn('@nested-component-1 h2', 'foo')
        ->assertSeeIn('@nested-component-1 h3', '2')
        ->assertSeeIn('@nested-component-1 h4', 'bar')
        ->assertSeeIn('@nested-component-1 h5', '2')
        ->assertSeeIn('@nested-component-2 h2', 'foo')
        ->assertSeeIn('@nested-component-2 h3', '2')
        ->assertSeeIn('@nested-component-2 h4', 'bar')
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
        ->tap($this->startListeningForFetchedHtml(...))
        ->assertSeeIn('h1', '1')
        ->assertSeeIn('@nested-component-1 h2', 'foo')
        ->assertSeeIn('@nested-component-1 h3', '1')
        ->assertSeeIn('@nested-component-1 h4', 'bar')
        ->assertSeeIn('@nested-component-1 h5', '1')
        ->assertSeeIn('@nested-component-2 h2', 'foo')
        ->assertSeeIn('@nested-component-2 h3', '2')
        ->assertSeeIn('@nested-component-2 h4', 'bar')
        ->assertSeeIn('@nested-component-2 h5', '2')
        ->waitForLivewire()->click('@button')
        ->tap($this->assertFetchedHtmlIsMissing('foo'))
        ->tap($this->assertFetchedHtmlIsMissing('bar'))
        ->assertSeeIn('h1', '2')
        ->assertSeeIn('@nested-component-1 h2', 'foo')
        ->assertSeeIn('@nested-component-1 h3', '2')
        ->assertSeeIn('@nested-component-1 h4', 'bar')
        ->assertSeeIn('@nested-component-1 h5', '2')
        ->assertSeeIn('@nested-component-2 h2', 'foo')
        ->assertSeeIn('@nested-component-2 h3', '3')
        ->assertSeeIn('@nested-component-2 h4', 'bar')
        ->assertSeeIn('@nested-component-2 h5', '3')
        ;
    }

    protected function startListeningForFetchedHtml($browser)
    {
        $browser->script('
            window.Livewire.hook("commit", ({ component, commit, respond, succeed, fail }) => {
                respond(({ snapshot, effects }) => {
                    window.__last_html = effects.html
                })
            });
            return "";
        ');
    }

    protected function assertFetchedHtmlIsMissing($browser)
    {
        return function ($browser) {
            $html = $browser->script(['return window.__last_html'])[0];

            $this->assertStringNotContainsString('foo', $html);
        };
    }

    protected function assertFetchedHtmlContains($expected)
    {
        return function ($browser) use ($expected) {
            $html = $browser->script(['return window.__last_html'])[0];

            $this->assertStringContainsString($expected, $html);
        };
    }
}
