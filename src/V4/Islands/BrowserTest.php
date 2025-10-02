<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_an_inline_island_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island
                        <div dusk="island">
                            <p>Island content</p>
                            <p dusk="island-count">Island count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="island-count-button">Island increment count</button>
                        </div>
                    @endisland

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island', 'Island content')

        ->assertSeeIn('@island-count', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render the island, not the component...
        ->waitForLivewire()->click('@island-count-button')
        ->assertSeeIn('@island-count', 'Island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-count', 'Island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_sibling_inline_islands_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island
                        <div dusk="island-1">
                            <p>Island 1</p>
                            <p dusk="island-1-count">Island 1 count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="island-1-count-button">Island 1 increment count</button>
                        </div>
                    @endisland

                    @island
                        <div dusk="island-2">
                            <p>Island 2</p>
                            <p dusk="island-2-count">Island 2 count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="island-2-count-button">Island 2 increment count</button>
                        </div>
                    @endisland

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island-1', 'Island 1')
        ->assertSeeIn('@island-2', 'Island 2')

        ->assertSeeIn('@island-1-count', 'Island 1 count: 0')
        ->assertSeeIn('@island-2-count', 'Island 2 count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render that island, not the component or other islands...
        ->waitForLivewire()->click('@island-1-count-button')
        ->assertSeeIn('@island-1-count', 'Island 1 count: 1')
        ->assertSeeIn('@island-2-count', 'Island 2 count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render that island, not the other island...
        ->waitForLivewire()->click('@island-2-count-button')
        ->assertSeeIn('@island-1-count', 'Island 1 count: 1')
        ->assertSeeIn('@island-2-count', 'Island 2 count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-1-count', 'Island 1 count: 1')
        ->assertSeeIn('@island-2-count', 'Island 2 count: 2')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }

    public function test_nested_inline_islands_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island
                        <div dusk="outer-island">
                            <p>Outer island content</p>
                            <p dusk="outer-island-count">Outer island count: {{ $count }}</p>
                            <button wire:click="incrementCount" dusk="outer-island-count-button">Outer island increment count</button>

                            @island
                                <div dusk="inner-island">
                                    <p>Inner island content</p>
                                    <p dusk="inner-island-count">Inner island count: {{ $count }}</p>

                                    <button wire:click="incrementCount" dusk="inner-island-count-button">Inner island increment count</button>
                                </div>
                            @endisland
                        </div>
                    @endisland

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@outer-island', 'Outer island content')
        ->assertSeeIn('@inner-island', 'Inner island content')

        ->assertSeeIn('@outer-island-count', 'Outer island count: 0')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A nested island action should only re-render that island, not the component or any wrapping islands...
        ->waitForLivewire()->click('@inner-island-count-button')
        ->assertSeeIn('@outer-island-count', 'Outer island count: 0')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A island action should only re-render that island but not the component or any nested islands...
        ->waitForLivewire()->click('@outer-island-count-button')
        ->assertSeeIn('@outer-island-count', 'Outer island count: 2')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@outer-island-count', 'Outer island count: 2')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }

    public function test_an_external_view_island_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(view: 'islands::external-island')

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@external-island', 'External island content')

        ->assertSeeIn('@external-island-count', 'External island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render that island, not the component...
        ->waitForLivewire()->click('@external-island-count-button')
        ->assertSeeIn('@external-island-count', 'External island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@external-island-count', 'External island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_an_inline_island_can_be_deferred()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(defer: true)
                            <div dusk="island">
                                <p>Island content</p>
                            </div>
                        @endisland
                    </div>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSeeNothingIn('@island-container')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_an_inline_island_can_be_lazy()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    <div style="height: 100vh">Long content to push the island off the page...</div>

                    <div dusk="island-container">
                        @island(lazy: true)
                            <div dusk="island">
                                <p>Island content</p>
                            </div>
                        @endisland
                    </div>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSeeNothingIn('@island-container')
        ->waitForNoLivewire()

        // Wait for the island to be hydrated if it was going to, but it shouldn't...
        ->pause(600)

        ->assertDontSee('Island content')

        ->scrollIntoView('@island-container')
        ->assertSeeNothingIn('@island-container')

        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_sibling_external_view_islands_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(view: 'islands::external-island-1')

                    @island(view: 'islands::external-island-2')

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@external-island-1', 'External island 1 content')
        ->assertSeeIn('@external-island-2', 'External island 2 content')

        ->assertSeeIn('@external-island-1-count', 'External island 1 count: 0')
        ->assertSeeIn('@external-island-2-count', 'External island 2 count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render that island, not the component or other islands...
        ->waitForLivewire()->click('@external-island-1-count-button')
        ->assertSeeIn('@external-island-1-count', 'External island 1 count: 1')
        ->assertSeeIn('@external-island-2-count', 'External island 2 count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render that island, not the component or other islands...
        ->waitForLivewire()->click('@external-island-2-count-button')
        ->assertSeeIn('@external-island-1-count', 'External island 1 count: 1')
        ->assertSeeIn('@external-island-2-count', 'External island 2 count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@external-island-1-count', 'External island 1 count: 1')
        ->assertSeeIn('@external-island-2-count', 'External island 2 count: 2')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }

    public function test_an_inline_island_can_be_mode_append_and_render_always()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(mode: 'append', render: 'always')
                            <p>Island count: {{ $count }}</p>
                        @endisland
                    </div>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 0
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 1')
        ->assertDontSeeIn('@island-container', 'Island count: 2')
        ->assertDontSeeIn('@island-container', 'Island count: 3')

        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 0
            Island count: 1
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 2')
        ->assertDontSeeIn('@island-container', 'Island count: 3')

        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 0
            Island count: 1
            Island count: 2
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 3')
        ;
    }

    public function test_an_inline_island_can_be_mode_prepend_and_render_always()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(mode: 'prepend', render: 'always')
                            <p>Island count: {{ $count }}</p>
                        @endisland
                    </div>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 0
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 1')
        ->assertDontSeeIn('@island-container', 'Island count: 2')
        ->assertDontSeeIn('@island-container', 'Island count: 3')

        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 1
            Island count: 0
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 2')
        ->assertDontSeeIn('@island-container', 'Island count: 3')

        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn(
            '@island-container',
            <<<'HTML'
            Island count: 2
            Island count: 1
            Island count: 0
            HTML
        )
        ->assertDontSeeIn('@island-container', 'Island count: 3')
        ;
    }

    public function test_an_island_is_rendered_by_the_component_once_by_default()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island
                            <p>Island count: {{ $count }}</p>
                        @endisland
                    </div>

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()

        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component should not re-render the island by default...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 1')

        // A component should not re-render the island by default...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_an_island_is_rendered_by_the_component_every_time_if_render_is_set_to_always()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(render: 'always')
                            <p>Island count: {{ $count }}</p>
                        @endisland
                    </div>

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()

        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component should re-render the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 1')

        // A component should re-render the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_an_island_is_not_rendered_by_the_component_if_render_is_set_to_skip()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(render: 'skip')
                            <p>Island content</p>
                        @endisland
                    </div>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeNothingIn('@island-container')
        ;
    }

    public function test_a_named_inline_island_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island('bob')
                        <div dusk="bob-island">
                            <p>Bob island content</p>
                            <p dusk="bob-island-count">Bob island count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="bob-island-count-button">Bob island increment count</button>
                        </div>
                    @endisland

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@bob-island', 'Bob island content')

        ->assertSeeIn('@bob-island-count', 'Bob island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // An island action should only re-render the island, not the component...
        ->waitForLivewire()->click('@bob-island-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_two_named_islands_can_have_the_same_name_and_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island('bob')
                        <div dusk="bob-island">
                            <p>Bob island content</p>
                            <p dusk="bob-island-count">Bob island count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="bob-island-count-button">Bob island increment count</button>
                        </div>
                    @endisland

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>

                    @island('bob')
                        <div dusk="bob-after-island">
                            <p>Bob after island content</p>
                            <p dusk="bob-after-island-count">Bob after island count: {{ $count }}</p>

                            <button wire:click="incrementCount" dusk="bob-after-island-count-button">Bob after island increment count</button>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@bob-island', 'Bob island content')
        ->assertSeeIn('@bob-after-island', 'Bob after island content')

        ->assertSeeIn('@bob-island-count', 'Bob island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@bob-after-island-count', 'Bob after island count: 0')

        // An island action should re-render any islands with the same name, but not the component...
        ->waitForLivewire()->click('@bob-island-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@bob-after-island-count', 'Bob after island count: 1')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ->assertSeeIn('@bob-after-island-count', 'Bob after island count: 1')
        ;
    }

    public function test_nested_external_view_islands_can_be_interacted_with()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(view: 'islands::outer-external-island')

                    <p dusk="component-count">Component count: {{ $count }}</p>

                    <button wire:click="incrementCount" dusk="component-count-button">Component increment count</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@outer-external-island', 'Outer external island content')
        ->assertSeeIn('@inner-external-island', 'Inner external island content')

        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 0')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A nested island action should only re-render that island, not the component or any wrapping islands...
        ->waitForLivewire()->click('@inner-external-island-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 0')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A island action should only re-render that island, but not the component or any nested islands...
        ->waitForLivewire()->click('@outer-external-island-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 2')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 2')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }

    public function test_a_deferred_inline_island_can_be_passed_a_placeholder_parameter()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(defer: true, placeholder: 'Custom placeholder!')
                        <div dusk="island">
                            <p>Island content</p>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Custom placeholder!')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_deferred_inline_island_can_have_a_placeholder_directive()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(defer: true)
                        @placeholder
                            <p>Directive based placeholder!</p>
                        @endplaceholder

                        <div dusk="island">
                            <p>Island content</p>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Directive based placeholder!')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_deferred_external_island_can_be_passed_a_placeholder_parameter()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(defer: true, view: 'islands::external-basic-island', placeholder: 'Custom placeholder!')
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@external-basic-island')
        ->assertSee('Custom placeholder!')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('External island content')
        ->assertSeeIn('@external-basic-island', 'External island content')
        ;
    }

    public function test_a_deferred_external_island_can_have_a_placeholder_directive()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(defer: true, view: 'islands::external-island-with-placeholder')
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@external-island-with-placeholder')
        ->assertSee('External island placeholder content')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('External island content')
        ->assertSeeIn('@external-island-with-placeholder', 'External island content')
        ;
    }

    public function test_a_deferred_inline_island_with_mode_append_replaces_placeholder_but_still_appends_new_content()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island">
                        @island('foo', defer: true, mode: 'append')
                            @placeholder
                                <p>Directive based placeholder!</p>
                            @endplaceholder

                            <p>Island content {{ $count }}</p>
                        @endisland
                    </div>

                    <button wire:click="incrementCount" wire:island="foo" dusk="increment-count-button">Increment count</button>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island', 'Directive based placeholder!')
        ->assertDontSeeIn('@island', 'Island content')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertDontSeeIn('@island', 'Island content 1')

        ->waitForLivewire()->click('@increment-count-button')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertSeeIn('@island', 'Island content 1')
        ;
    }

    public function test_a_lazy_inline_island_can_be_passed_a_placeholder_parameter()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(lazy: true, placeholder: 'Custom placeholder!')
                        <div dusk="island">
                            <p>Island content</p>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Custom placeholder!')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_lazy_inline_island_can_have_a_placeholder_directive()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    @island(lazy: true)
                        @placeholder
                            <p>Directive based placeholder!</p>
                        @endplaceholder

                        <div dusk="island">
                            <p>Island content</p>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Directive based placeholder!')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_lazy_inline_island_with_mode_append_replaces_placeholder_but_still_appends_new_content()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function hydrate()
                {
                    usleep(500 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island">
                        @island('foo', lazy: true, mode: 'append')
                            @placeholder
                                <p>Directive based placeholder!</p>
                            @endplaceholder

                            <p>Island content {{ $count }}</p>
                        @endisland
                    </div>

                    <button wire:click="incrementCount" wire:island="foo" dusk="increment-count-button">Increment count</button>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island', 'Directive based placeholder!')
        ->assertDontSeeIn('@island', 'Island content')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('Island content')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertDontSeeIn('@island', 'Island content 1')

        ->waitForLivewire()->click('@increment-count-button')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertSeeIn('@island', 'Island content 1')
        ;
    }

    public function test_a_skipped_inline_island_can_be_passed_a_placeholder_parameter()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @island('foo', render: 'skip', placeholder: 'Custom placeholder!')
                            <div dusk="island">
                                <p>Island content</p>
                            </div>
                        @endisland

                        <button wire:click="$refresh" wire:island="foo" dusk="refresh-foo">Refresh</button>
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Custom placeholder!')

        ->waitForLivewire()->click('@refresh-foo')

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_skipped_inline_island_can_have_a_placeholder_directive()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @island('foo', render: 'skip')
                            @placeholder
                                <p>Directive based placeholder!</p>
                            @endplaceholder

                            <div dusk="island">
                                <p>Island content</p>
                            </div>
                        @endisland

                        <button wire:click="$refresh" wire:island="foo" dusk="refresh-foo">Refresh</button>
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Directive based placeholder!')

        ->waitForLivewire()->click('@refresh-foo')

        ->waitForText('Island content')
        ->assertSeeIn('@island', 'Island content')
        ;
    }

    public function test_a_skipped_inline_island_with_mode_append_replaces_placeholder_but_still_appends_new_content()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island">
                        @island('foo', render: 'skip', mode: 'append')
                            @placeholder
                                <p>Directive based placeholder!</p>
                            @endplaceholder

                            <p>Island content {{ $count }}</p>
                        @endisland
                    </div>

                    <button wire:click="$refresh" wire:island="foo" dusk="refresh-foo">Refresh</button>
                    <button wire:click="incrementCount" wire:island="foo" dusk="increment-count-button">Increment count</button>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island', 'Directive based placeholder!')
        ->assertDontSeeIn('@island', 'Island content')

        ->waitForLivewire()->click('@refresh-foo')

        ->waitForText('Island content')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertDontSeeIn('@island', 'Island content 1')

        ->waitForLivewire()->click('@increment-count-button')
        ->assertDontSeeIn('@island', 'Directive based placeholder!')
        ->assertSeeIn('@island', 'Island content 0')
        ->assertSeeIn('@island', 'Island content 1')
        ;
    }

    public function test_an_island_mode_can_be_temporarily_changed_using_the_wire_island_directive_modifiers()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function incrementCount()
                {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island">
                        @island('foo', mode: 'append'){{ $count }}@endisland
                    </div>

                    <button wire:click="incrementCount" wire:island="foo" dusk="default-increment-button">Default</button>
                    <button wire:click="incrementCount" wire:island.prepend="foo" dusk="prepend-increment-button">Prepend</button>
                    <button wire:click="incrementCount" wire:island.append="foo" dusk="append-increment-button">Append</button>
                    <button wire:click="incrementCount" wire:island.replace="foo" dusk="replace-increment-button">Replace</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@island', '0')

            ->waitForLivewire()->click('@default-increment-button')
            ->assertSeeIn('@island', '01')

            ->waitForLivewire()->click('@prepend-increment-button')
            ->assertSeeIn('@island', '201')

            ->waitForLivewire()->click('@append-increment-button')
            ->assertSeeIn('@island', '2013')

            ->waitForLivewire()->click('@replace-increment-button')
            ->assertSeeIn('@island', '4')
            ;
    }

    public function test_an_island_can_be_bypassed_by_the_component_when_the_component_re_renders()
    {
        // Need to use a table here, because if the island contents that are being re-rendered by the component have a placeholer div,
        // then the div will be hoisted out of the table by the browser, which was causing the island contents to disappear...
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh-component">Refresh</button>

                        <table dusk="island">
                            @island('foo')
                                @foreach(range(1, 3) as $i)
                                    <tr>
                                        <td>Island content {{ $i }}</td>
                                    </tr>
                                @endforeach
                            @endisland
                        </table>
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertSeeIn('@island', 'Island content 1')
        ->assertSeeIn('@island', 'Island content 2')
        ->assertSeeIn('@island', 'Island content 3')

        ->waitForLivewire()->click('@refresh-component')
        ->assertSeeIn('@island', 'Island content 1')
        ->assertSeeIn('@island', 'Island content 2')
        ->assertSeeIn('@island', 'Island content 3')
        ;
    }

    public function test_wire_poll_works_inside_an_island()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function hydrate() {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <div dusk="component-count">Component count: {{ $count }}</div>

                        @island
                            <div wire:poll.250ms dusk="island-count">Island count: {{ $count }}</div>
                        @endisland
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 0')

        // Wait for a poll to have happened...
        ->pause(300)
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 1')

        ->pause(250)
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 2')
        ;
    }

    public function test_wire_poll_and_wire_loading_works_inside_an_island()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function hydrate() {
                    $this->count++;
                    usleep(200 * 1000); // 200ms
                }

                public function render() { return <<<'HTML'
                    <div>
                        <div dusk="component-count">Component count: {{ $count }}</div>
                        <div wire:loading dusk="component-loading">Component loading</div>

                        @island
                            <div wire:poll.500ms dusk="island-count">Island count: {{ $count }}</div>
                            <div wire:loading dusk="island-loading">Island loading</div>
                        @endisland
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 0')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')


        // Wait for a poll to have started...
        ->waitForText('Island loading')
        ->assertMissing('@component-loading')
        ->assertSeeIn('@island-loading', 'Island loading')

        // Wait for the poll to have finished...
        ->waitForText('Island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 1')
        ->waitUntilMissingText('Island loading')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')

        // Wait for the poll to have started...
        ->waitForText('Island loading')
        ->assertMissing('@component-loading')
        ->assertSeeIn('@island-loading', 'Island loading')

        // Wait for the poll to have finished...
        ->waitForText('Island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 2')
        ->waitUntilMissingText('Island loading')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')
        ;
    }

    public function test_an_island_can_accept_a_poll_parameter()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function hydrate() {
                    $this->count++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <div dusk="component-count">Component count: {{ $count }}</div>

                        @island (poll: '250ms')
                            <div dusk="island-count">Island count: {{ $count }}</div>
                        @endisland
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 0')

        // Wait for a poll to have happened...
        ->pause(300)
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 1')

        ->pause(250)
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 2')
        ;
    }

    public function test_island_poll_parameter_works_with_wire_loading_inside_an_island()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $count = 0;

                public function hydrate() {
                    $this->count++;
                    usleep(200 * 1000); // 200ms
                }

                public function render() { return <<<'HTML'
                    <div>
                        <div dusk="component-count">Component count: {{ $count }}</div>
                        <div wire:loading dusk="component-loading">Component loading</div>

                        @island (poll: '500ms')
                            <div dusk="island-count">Island count: {{ $count }}</div>
                            <div wire:loading dusk="island-loading">Island loading</div>
                        @endisland
                    </div>
                    HTML;
                }
            }
        )
        ->waitForLivewireToLoad()
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 0')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')


        // Wait for a poll to have started...
        ->waitForText('Island loading')
        ->assertMissing('@component-loading')
        ->assertSeeIn('@island-loading', 'Island loading')

        // Wait for the poll to have finished...
        ->waitForText('Island count: 1')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 1')
        ->waitUntilMissingText('Island loading')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')

        // Wait for the poll to have started...
        ->waitForText('Island loading')
        ->assertMissing('@component-loading')
        ->assertSeeIn('@island-loading', 'Island loading')

        // Wait for the poll to have finished...
        ->waitForText('Island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')
        ->assertSeeIn('@island-count', 'Island count: 2')
        ->waitUntilMissingText('Island loading')
        ->assertMissing('@component-loading')
        ->assertMissing('@island-loading')
        ;
    }
}
