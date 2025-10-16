<?php

namespace Livewire\Features\SupportIslands;

use Tests\BrowserTestCase;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_island_directive()
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

    public function test_sibling_islands()
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

                    @island
                        <button type="button" wire:click="increment" dusk="sibling-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-increment', 'Count: 0')
            ->assertSeeIn('@sibling-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@sibling-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@sibling-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@sibling-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@sibling-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 3')
            ;
    }

    public function test_render_nested_islands()
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

                        @island
                            <button type="button" wire:click="increment" dusk="nested-island-increment">Count: {{ $count }}</button>
                        @endisland
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-increment', 'Count: 0')
            ->assertSeeIn('@nested-island-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@nested-island-increment', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@nested-island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@nested-island-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@nested-island-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 3')
            ;
    }

    public function test_always_render_island()
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
                    @island(always: true)
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
            ->assertSeeIn('@island-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 2')
            ;
    }

    public function test_skip_render_island()
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
                    @island(name: 'foo', skip: true)
                        @placeholder
                            <p dusk="island-placeholder">Loading...</p>
                        @endplaceholder

                        <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>
                    @endisland

                    <button type="button" wire:click="increment" wire:island="foo" dusk="foo-increment">Root count: {{ $count }}</button>

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertNotPresent('@island-increment')
            ->assertPresent('@island-placeholder')
            ->assertSeeIn('@foo-increment', 'Root count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@foo-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertSeeIn('@foo-increment', 'Root count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 2')
            ->assertSeeIn('@foo-increment', 'Root count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-increment', 'Count: 2')
            ->assertSeeIn('@foo-increment', 'Root count: 3')
            ->assertSeeIn('@root-increment', 'Root count: 3')
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
                    <div style="height: 200vh" dusk="long-content">Long content to push the island off the page...</div>

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
            ->scrollTo('@root-increment')
            ->waitForText('Count: 0')
            ->assertPresent('@island-increment')
            ->assertPresent('@root-increment')
            ;
    }

    public function test_defer_island()
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
                    @island(defer: true)
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

    public function test_two_islands_with_the_same_name()
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
                    @island(name: 'foo')
                        <div dusk="foo-island-1">Count: {{ $count }}</div>
                    @endisland

                    @island(name: 'foo')
                        <div dusk="foo-island-2">Count: {{ $count }}</div>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>

                    <button type="button" wire:click="increment" dusk="foo-increment" wire:island="foo">Increment Foo</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@foo-island-1', 'Count: 0')
            ->assertSeeIn('@foo-island-2', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@foo-island-1', 'Count: 0')
            ->assertSeeIn('@foo-island-2', 'Count: 0')
            ->assertSeeIn('@root-increment', 'Root count: 1')
            ->waitForLivewire()->click('@foo-increment')
            ->assertSeeIn('@foo-island-1', 'Count: 2')
            ->assertSeeIn('@foo-island-2', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 1')
            ;
    }

    public function test_render_island_method()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;

                $this->renderIsland('foo');
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'foo')
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
            ->assertSeeIn('@island-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 2')
            ;
    }

    public function test_stream_island_method()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;

                $this->streamIsland('foo');
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'foo')
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
            ->assertSeeIn('@island-increment', 'Count: 2')
            ->assertSeeIn('@root-increment', 'Root count: 2')
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

    public function test_streams_append_into_island_over_time()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public function send()
            {
                $this->streamIsland('foo', 'Hi, how are you?', mode: 'append');
                usleep(250000);
                $this->streamIsland('foo', ' I hope things are going well.', mode: 'append');
                usleep(250000);
                $this->streamIsland('foo', ' I just wanted to check in.', mode: 'append');
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <div dusk="foo-island">
                        @island(name: 'foo')@endisland
                    </div>

                    <button type="button" wire:click="send" dusk="send">Send</button>
                </div>
                HTML;
            }
        }])
            ->assertPresent('@send')
            ->assertPresent('@foo-island')
            ->assertDontSee('Hi, how are you?')
            ->click('@send')
            ->waitForText('Hi, how are you?')
            ->assertSeeIn('@foo-island', 'Hi, how are you?')
            ->waitForText('I hope things are going well.')
            ->assertSeeIn('@foo-island', 'Hi, how are you? I hope things are going well.')
            ->waitForText('I just wanted to check in.')
            ->assertSeeIn('@foo-island', 'Hi, how are you? I hope things are going well. I just wanted to check in.')
            ;
    }

    public function test_island_works_with_error_bag()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $foo = '';

            public function validateFoo()
            {
                $this->validate(['foo' => 'required']);
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island
                        <button type="button" wire:click="validateFoo" dusk="island-validate-foo">Validate Foo</button>

                        <div>
                            Error: <div dusk="island-error">{{ $errors->first('foo') }}</div>
                        </div>
                    @endisland

                    <div>
                        Error: <div dusk="root-error">{{ $errors->first('foo') }}</div>
                    </div>
                </div>
                HTML;
            }
        }])
            ->assertDontSeeIn('@island-error', 'The foo field is required.')
            ->assertDontSeeIn('@root-error', 'The foo field is required.')
            ->waitForLivewire()->click('@island-validate-foo')
            ->assertSeeIn('@island-error', 'The foo field is required.')
            ->assertDontSeeIn('@root-error', 'The foo field is required.')
            ;
    }
}
