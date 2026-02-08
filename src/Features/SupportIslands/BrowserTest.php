<?php

namespace Livewire\Features\SupportIslands;

use Tests\BrowserTestCase;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');
        };
    }

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

    public function test_island_renders_inside_lazy_loaded_component()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        @island
                            <div dusk="island-content">Island loaded</div>
                        @endisland
                    </div>
                    HTML;
                }
            },
        ])
            ->waitFor('@island-content')
            ->assertSeeIn('@island-content', 'Island loaded');
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

    public function test_islands_inside_a_lazy_island_get_rendered_when_the_lazy_island_is_mounted()
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
                        <button type="button" wire:click="increment" dusk="outer-island-increment">Outer Island Count: {{ $count }}</button>

                        @island
                            <button type="button" wire:click="increment" dusk="inner-island-increment">Inner Island Count: {{ $count }}</button>
                        @endisland
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertNotPresent('@outer-island-increment')
            ->assertNotPresent('@inner-island-increment')
            ->assertPresent('@root-increment')
            ->scrollTo('@root-increment')
            ->waitForText('Outer Island Count: 0')
            ->assertPresent('@outer-island-increment')
            ->assertPresent('@inner-island-increment')
            ->assertPresent('@root-increment')
            ;
    }

    public function test_lazy_islands_inside_a_lazy_island_get_mounted_after_the_outer_lazy_island_is_mounted()
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
                        <button type="button" wire:click="increment" dusk="outer-island-increment">Outer Island Count: {{ $count }}</button>

                        @island(lazy: true)
                            <button type="button" wire:click="increment" dusk="inner-island-increment">Inner Island Count: {{ $count }}</button>
                        @endisland
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Root count: {{ $count }}</button>
                </div>
                HTML;
            }
        }])
            ->assertNotPresent('@outer-island-increment')
            ->assertNotPresent('@inner-island-increment')
            ->assertPresent('@root-increment')
            ->scrollTo('@root-increment')
            ->waitForText('Outer Island Count: 0')
            ->assertPresent('@outer-island-increment')
            ->assertNotPresent('@inner-island-increment')
            ->assertPresent('@root-increment')
            ->waitForText('Inner Island Count: 0')
            ->assertPresent('@inner-island-increment')
            ->assertPresent('@root-increment')
            ;
    }

    public function test_island_with_lazy_and_always_updates_with_the_component_when_the_component_makes_a_request()
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
                    <button type="button" wire:click="increment" dusk="root-increment">Count: {{ $count }}</button>

                    @island(lazy: true, always: true)
                        <button type="button" wire:click="increment" dusk="island-increment">Island Count: {{ $count }}</button>
                    @endisland
                </div>

                @script
                <script>
                    window.requestCount = 0

                    this.interceptMessage(() => {
                        window.requestCount++
                    })
                </script>
                @endscript
                HTML;
            }
        }])
            ->waitForLivewireToLoad()
            ->waitForText('Island Count: 0')
            ->assertScript('window.requestCount', 1) // Initial lazy island load
            ->assertSeeIn('@root-increment', 'Count: 0')
            ->assertSeeIn('@island-increment', 'Island Count: 0')
            ->tap(fn ($b) => $b->script('window.requestCount = 0')) // Reset counter
            ->waitForLivewire()->click('@root-increment')
            ->pause(100)
            ->assertScript('window.requestCount', 1) // Should be only 1 request for both component and island
            ->assertSeeIn('@root-increment', 'Count: 1')
            ->assertSeeIn('@island-increment', 'Island Count: 1')
            ;
    }

    public function test_renderless_attribute_skips_island_render()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            #[\Livewire\Attributes\Renderless]
            public function incrementRenderless()
            {
                $this->count++;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'foo')
                        <div dusk="island-count">Count: {{ $count }}</div>
                    @endisland

                    <button type="button" wire:click="increment" dusk="increment" wire:island="foo">Increment</button>
                    <button type="button" wire:click="incrementRenderless" dusk="increment-renderless" wire:island="foo">Increment Renderless</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-count', 'Count: 0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@island-count', 'Count: 1')
            ->waitForLivewire()->click('@increment-renderless')
            // The count was incremented server-side but the island should NOT re-render...
            ->assertSeeIn('@island-count', 'Count: 1')
            ->waitForLivewire()->click('@increment')
            // Now the island should show the updated count (including the renderless increment)...
            ->assertSeeIn('@island-count', 'Count: 3')
            ;
    }

    public function test_renderless_modifier_skips_island_render()
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
                        <div dusk="island-count">Count: {{ $count }}</div>
                    @endisland

                    <button type="button" wire:click="increment" dusk="increment" wire:island="foo">Increment</button>
                    <button type="button" wire:click.renderless="increment" dusk="increment-renderless" wire:island="foo">Increment Renderless</button>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-count', 'Count: 0')
            ->waitForLivewire()->click('@increment-renderless')
            // The count was incremented server-side but the island should NOT re-render...
            ->assertSeeIn('@island-count', 'Count: 0')
            ->waitForLivewire()->click('@increment')
            // Now the island should show the updated count (including the renderless increment)...
            ->assertSeeIn('@island-count', 'Count: 2')
            ;
    }

    public function test_wire_island_calls_method_scoped_to_island()
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
                        <div dusk="island-count">Count: {{ $count }}</div>
                    @endisland

                    <button type="button" x-on:click="$wire.$island('foo').increment()" dusk="island-increment">Increment Island</button>

                    <div dusk="root-count">Root count: {{ $count }}</div>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-count', 'Count: 0')
            ->assertSeeIn('@root-count', 'Root count: 0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-count', 'Count: 1')
            ->assertSeeIn('@root-count', 'Root count: 0')
            ;
    }

    public function test_wire_island_refresh_scoped_to_island()
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
                        <div dusk="island-count">Count: {{ $count }}</div>
                    @endisland

                    <button type="button" wire:click="increment" dusk="root-increment">Increment</button>

                    <button type="button" x-on:click="$wire.$island('foo').$refresh()" dusk="island-refresh">Refresh Island</button>

                    <div dusk="root-count">Root count: {{ $count }}</div>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-count', 'Count: 0')
            ->assertSeeIn('@root-count', 'Root count: 0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-count', 'Count: 0')
            ->assertSeeIn('@root-count', 'Root count: 1')
            ->waitForLivewire()->click('@island-refresh')
            ->assertSeeIn('@island-count', 'Count: 1')
            ->assertSeeIn('@root-count', 'Root count: 1')
            ;
    }

    public function test_wire_island_with_append_mode()
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

                    <button type="button" x-on:click="$wire.$island('foo', { mode: 'append' }).increment()" dusk="append-increment">Append</button>
                    <button type="button" x-on:click="$wire.$island('foo', { mode: 'prepend' }).increment()" dusk="prepend-increment">Prepend</button>
                </div>
                HTML;
            }
        }])
            ->assertSourceHas('<div>Count: 0</div>')
            ->waitForLivewire()->click('@append-increment')
            ->assertSourceHas('<div>Count: 0</div><div>Count: 1</div>')
            ->waitForLivewire()->click('@prepend-increment')
            ->assertSourceHas('<div>Count: 2</div><div>Count: 0</div><div>Count: 1</div>')
            ;
    }

    public function test_island_poll_does_not_trigger_named_view_transition_outside_island()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    <div wire:transition="step">
                        <div dusk="content">Content</div>
                    </div>

                    @island
                        <div wire:poll.1s dusk="island-poll">Poll island</div>
                    @endisland
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@content', 'Content')
            // Intercept document.startViewTransition to track if it gets called...
            ->tap(fn ($b) => $b->script("
                window.__viewTransitionCount = 0;
                let orig = document.startViewTransition.bind(document);
                document.startViewTransition = function() {
                    window.__viewTransitionCount++;
                    return orig.apply(document, arguments);
                };
            "))
            // Wait for at least one poll cycle to complete...
            ->pause(1500)
            // Assert no view transitions were triggered by the island poll...
            ->assertScript('window.__viewTransitionCount', 0)
        ;
    }

    public function test_named_view_transition_inside_island_still_works()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $step = 1;

            public function nextStep()
            {
                $this->step = 2;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island
                        <div wire:transition="step" wire:key="step-{{ $step }}">
                            <div dusk="step-display">Step {{ $step }}</div>
                        </div>

                        <button wire:click="nextStep" dusk="next-step">Next</button>
                    @endisland
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@step-display', 'Step 1')
            // Intercept document.startViewTransition to track if it gets called...
            ->tap(fn ($b) => $b->script("
                window.__viewTransitionCount = 0;
                let orig = document.startViewTransition.bind(document);
                document.startViewTransition = function() {
                    window.__viewTransitionCount++;
                    return orig.apply(document, arguments);
                };
            "))
            // Click the button to trigger a transition inside the island...
            ->waitForLivewire()->click('@next-step')
            // Wait for the transition to complete and content to update...
            ->waitForTextIn('@step-display', 'Step 2')
            // Assert a view transition was triggered...
            ->assertScript('window.__viewTransitionCount', 1)
        ;
    }

    public function test_more_than_ten_islands_using_single_file_component()
    {
        Livewire::visit('twelve-islands')
            ->assertSee('island test 1')
            ->assertSee('island test 2')
            ->assertSee('island test 3')
            ->assertSee('island test 4')
            ->assertSee('island test 5')
            ->assertSee('island test 6')
            ->assertSee('island test 7')
            ->assertSee('island test 8')
            ->assertSee('island test 9')
            ->assertSee('island test 10')
            ->assertSee('island test 11')
            ->assertDontSee('STARTISLAND')
            ->assertDontSee('ENDISLAND')
            ;
    }
}
