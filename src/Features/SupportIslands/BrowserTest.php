<?php

namespace Livewire\Features\SupportIslands;

use Tests\BrowserTestCase;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_render_island_directives()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island
                        <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@root-increment', 'Root count: 2')
            ;
    }

    public function test_lazy_island()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function hydrate() { usleep(250000); } // 250ms

            public function render() {
                return <<<'HTML'
                <div>
                    @island(lazy: true)
                        <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertNotPresent('@island-increment')
            ->assertPresent('@root-increment')
            ->waitForText('Count: 0')
            ->assertPresent('@island-increment')
            ->assertPresent('@root-increment')
            ;
    }

    public function test_lazy_with_placeholder()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function hydrate() { usleep(250000); } // 250ms

            public function render() {
                return <<<'HTML'
                <div>
                    @island(lazy: true)
                        @placeholder
                            <p>Loading...</p>
                        @endplaceholder

                        <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertNotPresent('@island-increment')
            ->assertPresent('@root-increment')
            ->assertSee('Loading...')
            ->waitForText('Count: 0')
            ->assertPresent('@island-increment')
            ->assertPresent('@root-increment')
            ->assertDontSee('Loading...')
            ->assertSeeIn('@island-increment', 'Count: 0')
            ;
    }

    public function test_named_islands()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function hydrate() { usleep(250000); } // 250ms

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'foo')
                        <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>

                    <button type="button" wire:click="increment" dusk="foo-increment" wire:island="foo">Foo count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->assertSeeIn('@foo-increment', 'Foo count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->assertSeeIn('@foo-increment', 'Foo count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@root-increment', 'Root count: 2')
            ->assertSeeIn('@foo-increment', 'Foo count: 2')
            ->waitForLivewire()->click('@foo-increment')
            ->assertSeeIn('@island-increment', 'Count: 3')
            ->assertSeeIn('@root-increment', 'Root count: 2')
            ->assertSeeIn('@foo-increment', 'Foo count: 2')
            ;
    }

    public function test_append_and_prepend_islands()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <div dusk="foo-island">
                        @island(name: 'foo')<div>Count: {{ $count }}</div>@endisland
                    </div>

                    <button type="button" wire:click="increment" dusk="foo-increment" wire:island="foo">Increment</button>
                    <button type="button" wire:click="increment" dusk="foo-prepend-increment" wire:island.prepend="foo">Prepend</button>
                    <button type="button" wire:click="increment" dusk="foo-append-increment" wire:island.append="foo">Append</button>
                </div>
                HTML;
            }
        }])
            ->assertSourceHas('<div>Count: 0</div>')
            ->waitForLivewire()->click('@foo-increment')
            ->assertSourceHas('<div>Count: 1</div>')
            ->waitForLivewire()->click('@foo-prepend-increment')
            ->assertSourceHas('<div>Count: 2</div><div>Count: 1</div>')
            ->waitForLivewire()->click('@foo-append-increment')
            ->assertSourceHas('<div>Count: 2</div><div>Count: 1</div><div>Count: 3</div>')
            ->waitForLivewire()->click('@foo-prepend-increment')
            ->assertSourceHas('<div>Count: 4</div><div>Count: 2</div><div>Count: 1</div><div>Count: 3</div>')
            ->waitForLivewire()->click('@foo-append-increment')
            ->assertSourceHas('<div>Count: 4</div><div>Count: 2</div><div>Count: 1</div><div>Count: 3</div><div>Count: 5</div>')
            ;
    }
}
