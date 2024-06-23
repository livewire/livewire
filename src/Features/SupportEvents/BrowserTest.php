<?php

namespace Livewire\Features\SupportEvents;

use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_can_listen_for_component_event_with_this_on_in_javascript()
    {
        Livewire::visit(new class extends Component {
            function foo() {
                $this->dispatch('foo');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="foo" dusk="button">Dispatch "foo"</button>

                    <span x-init="@this.on('foo', () => { $el.textContent = 'bar' })" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
        ->assertDontSeeIn('@target', 'bar')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@target', 'bar');
    }

    public function test_dont_call_render_on_renderless_event_handler()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $listeners = ['foo' => 'onFoo'];

            #[Renderless]
            function onFoo() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button @click="$dispatch('foo')" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_can_dispatch_self_inside_script_directive()
    {
        Livewire::visit(new class extends Component {
            public $foo = 'bar';

            #[On('trigger')]
            function changeFoo() {
                $this->foo = 'baz';
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <h1 dusk="output">{{ $foo }}</h1>
                </div>

                @script
                <script>
                    $wire.dispatchSelf('trigger')
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForTextIn('@output', 'baz');
    }

    public function test_dispatch_from_javascript_should_only_be_called_once()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $listeners = ['foo' => 'onFoo'];

            function onFoo()
            {
                $this->count++;
            }

            function render()
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button @click="$dispatch('foo')" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '0')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_can_dispatch_to_another_component_globally()
    {
        Livewire::visit([
            new class extends Component {
                public function dispatchToOtherComponent()
                {
                    $this->dispatch('foo', message: 'baz')->to('child');
                }

                function render()
                {
                    return <<<'HTML'
                    <div>
                        <button x-on:click="window.Livewire.dispatchTo('child', 'foo', { message: 'bar' })" dusk="button">Dispatch to child from Alpine</button>
                        <button wire:click="dispatchToOtherComponent" dusk="button2">Dispatch to child from Livewire</button>

                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $message = 'foo';

                protected $listeners = ['foo' => 'onFoo'];

                function onFoo($message)
                {
                    $this->message = $message;
                }

                function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1 dusk="output">{{ $message }}</h1>
                    </div>
                    HTML;
                }
            },
        ])
            ->assertSeeIn('@output', 'foo')
            ->waitForLivewire()->click('@button')
            ->waitForTextIn('@output', 'bar')
            // For some reason this is flaky?
            // ->waitForLivewire()->click('@button2')
            // ->waitForTextIn('@output', 'baz')
            ;
    }

    public function test_can_unregister_global_livewire_listener()
    {
        Livewire::visit(new class extends Component {
            function render()
            {
                return Blade::render(<<<'HTML'
                <div x-data="{
                    count: 0,
                    listener: null,
                    init() {
                        this.listener = Livewire.on('foo', () => { this.count++ })
                    },
                    removeListener() {
                        this.listener()
                    }
                }">
                    <span x-text="count" dusk="text"></span>
                    <button @click="Livewire.dispatch('foo')" dusk="dispatch">Dispatch Event</button>
                    <button @click="removeListener" dusk="removeListener">Remove Listener</button>
                </div>
                HTML);
            }
        })
            ->assertSeeIn('@text', '0')
            ->click('@dispatch')
            ->assertSeeIn('@text', '1')
            ->click('@removeListener')
            ->click('@dispatch')
            ->assertSeeIn('@text', '1')
        ;
    }

    public function test_can_use_event_data_in_alpine_for_loop_without_throwing_errors()
    {
        Livewire::visit(new class extends Component {
            function fetchItems()
            {
                $this->dispatch('items-fetched', items: [
                    ['id' => 1, 'name' => 'test 1'],
                    ['id' => 2, 'name' => 'test 2'],
                    ['id' => 3, 'name' => 'test 3'],
                ]);
            }

            function render()
            {
                return Blade::render(<<<'HTML'
                <div x-data="{ items: [] }" @items-fetched.window="items = $event.detail.items">
                    <button wire:click="fetchItems" dusk="button">Fetch Items</button>
                    <div dusk="text" x-text="items"></div>
                    <div id="root">
                        <h1 dusk="texst" x-text="items"></h1>
                        <template x-for="item in items" :key="item.id">
                            <div x-text="item.name"></div>
                        </template>
                    </div>
                </div>
                HTML);
            }
        })
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@text', '[object Object],[object Object],[object Object]')
            ->assertScript('document.getElementById(\'root\').querySelectorAll(\'div\').length', 3)
            ->assertConsoleLogMissingWarning('item is not defined');
    }
}
