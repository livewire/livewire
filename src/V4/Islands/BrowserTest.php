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
                    @island(defer: true)
                        <div dusk="island">
                            <p>Island content</p>
                        </div>
                    @endisland
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->assertMissing('@island')
        ->assertSee('Loading...')

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
        ->assertSee('Loading...')
        ->waitForNoLivewire()

        // Wait for the island to be hydrated if it was going to, but it shouldn't...
        ->pause(600)

        ->assertDontSee('Island content')

        ->scrollIntoView('@island-container')
        ->assertSeeIn('@island-container', 'Loading...')

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

        // A island action should only re-render that island and any nested islands, but not the component...
        ->waitForLivewire()->click('@outer-external-island-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 1')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component but not the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 1')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }
}
