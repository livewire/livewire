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

        // A component action should re-render the component and the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-count', 'Island count: 2')
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

        // A component action should re-render the component and the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-1-count', 'Island 1 count: 3')
        ->assertSeeIn('@island-2-count', 'Island 2 count: 3')
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

        // A island action should only re-render that island and any nested islands, but not the component...
        ->waitForLivewire()->click('@outer-island-count-button')
        ->assertSeeIn('@outer-island-count', 'Outer island count: 2')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 0')

        // A component action should re-render the component and the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@outer-island-count', 'Outer island count: 3')
        ->assertSeeIn('@inner-island-count', 'Inner island count: 3')
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

        // A component action should re-render the component and the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@external-island-count', 'External island count: 2')
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

        // A component action should re-render the component and the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@external-island-1-count', 'External island 1 count: 3')
        ->assertSeeIn('@external-island-2-count', 'External island 2 count: 3')
        ->assertSeeIn('@component-count', 'Component count: 3')
        ;
    }

    public function test_an_inline_island_can_be_mode_append()
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
                        @island(mode: 'append')
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

    public function test_an_inline_island_can_be_mode_prepend()
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
                        @island(mode: 'prepend')
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

    public function test_an_inline_island_can_be_mode_once()
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
                        @island(mode: 'once')
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

        // A component action normally re-renders the component and the island, but as island mode is 'once', it should not re-render the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 1')

        // A component action normally re-renders the component and the island, but as island mode is 'once', it should not re-render the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@island-container', 'Island count: 0')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ;
    }

    public function test_an_inline_island_can_be_mode_skip()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <div dusk="island-container">
                        @island(mode: 'skip')
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

        // A component action should re-render the component and the island...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 2')
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

        // An island action should re-render any islands with the same name, but not the component...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@bob-island-count', 'Bob island count: 2')
        ->assertSeeIn('@component-count', 'Component count: 2')
        ->assertSeeIn('@bob-after-island-count', 'Bob after island count: 2')
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

        // A component action should re-render the component and the islands...
        ->waitForLivewire()->click('@component-count-button')
        ->assertSeeIn('@outer-external-island-count', 'Outer external island count: 2')
        ->assertSeeIn('@inner-external-island-count', 'Inner external island count: 3')
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

    // @todo: Fix this test...
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
        ->tinker()
        ->assertMissing('@external-island-with-placeholder')
        ->assertSee('External island placeholder content')

        // Wait for the island to be hydrated...
        ->pause(500)

        ->waitForText('External island content')
        ->assertSeeIn('@external-island-with-placeholder', 'External island content')
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

    public function test_can_reference_an_external_view_island_and_it_inherits_scope()
    {
        $this->markTestSkipped('Decide if `$this->island()` should still be supported. How does data work otherwise?');

        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public $counter = 0;

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function changeItems()
                {
                    $this->counter = 1;

                    $this->items = ['baz', 'bob'];

                    $this->island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5]);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="button">Change Items</button>

                    <span dusk="counter">{{ $counter }}</span>

                    @if ($counter > 0)
                        <?php throw new \Exception('This should not be triggered'); ?>
                    @endif

                    <div>
                        @island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5])
                    </div>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSee('foo')
        ->assertSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '5')
        ->waitForLivewire()->click('@button')
        ->waitForText('baz')
        ->assertSee('baz')
        ->assertSee('bob')
        ->assertDontSee('foo')
        ->assertDontSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '6')
        ;
    }

    public function test_can_append_and_prepend_islands()
    {
        $this->markTestSkipped('Decide if `$this->island()` should still be supported. How does data work otherwise?');

        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function changeItems()
                {
                    $this->items = ['baz', 'bob'];

                    $this->island('items', 'islands::items');
                }

                public function prependItems()
                {
                    array_unshift($this->items, 'bar');

                    $this->island('items', 'islands::items', ['items' => ['bar']])->prepend();
                }

                public function appendItems()
                {
                    array_push($this->items, 'lob');

                    $this->island('items', 'islands::items', ['items' => ['lob']])->append();
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="change-button">Change Items</button>
                    <button wire:click="prependItems" dusk="prepend-button">Prepend Items</button>
                    <button wire:click="appendItems" dusk="append-button">Append Items</button>

                    <ul dusk="items">
                        @island('items', 'islands::items')
                    </ul>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSourceHas("<li>foo</li>\n<li>bar</li>")
        ->waitForLivewire()->click('@change-button')
        ->assertSourceHas("<li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@prepend-button')
        ->assertSourceHas("<li>bar</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@append-button')
        ->assertSourceHas("<li>bar</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>baz</li>\n<li>bob</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>lob</li>")
        ;
    }

    public function test_can_use_inline_islands()
    {
        $this->markTestSkipped('This feature only works in single file components and we don\'t have a way to test those yet');

        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public $counter = 0;

                public function changeItems()
                {
                    $this->counter = 1;

                    $this->items = ['baz', 'bob'];

                    $this->island('basic', ['otherCounter' => $this->counter + 5]);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="button">Change Items</button>

                    <span dusk="counter">{{ $counter }}</span>

                    @if ($counter > 0)
                        <?php throw new \Exception('This should not be triggered'); ?>
                    @endif

                    <div>
                        @island('basic', ['otherCounter' => $this->counter + 5])
                            <div>
                                <span dusk="other-counter">{{ $otherCounter }}</span>

                                @foreach ($items as $item)
                                    <div>{{ $item }}</div>
                                @endforeach
                            </div>
                        @endisland
                    </div>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSee('foo')
        ->assertSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '5')
        ->waitForLivewire()->click('@button')
        ->waitForText('baz')
        ->assertSee('baz')
        ->assertSee('bob')
        ->assertDontSee('foo')
        ->assertDontSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '6')
        ;
    }
}
