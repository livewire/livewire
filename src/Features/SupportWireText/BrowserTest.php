<?php

namespace Livewire\Features\SupportWireText;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_text_shows_on_init()
    {
        Livewire::visit(new class extends Component {
            public $text = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:text="text" dusk="label"></div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@label', 'foo');
    }

    public function test_wire_text_supports_template_literal_interpolation()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:text="`${count} selected`" dusk="label"></div>
                    <button wire:click="increment" dusk="increment">Increment</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@label', '0 selected')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@label', '1 selected');
    }

    public function test_wire_text_updates_when_property_changes()
    {
        Livewire::visit(new class extends Component {
            public $text = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:text="text" dusk="label"></div>
                    <button wire:click="$set('text', 'bar')" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@label', 'foo')
        ->waitForLivewire()->click('@change')
        ->assertSeeIn('@label', 'bar');
    }

    public function test_wire_text_does_not_call_server_methods()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function foo()
            {
                $this->count++;
                return 'foo-result';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:text="foo" dusk="implicit"></div>
                    <div wire:text="foo()" dusk="explicit"></div>
                    <div dusk="count">{{ $count }}</div>
                    <button wire:text="'Call foo'" wire:click="foo" dusk="call"></button>
                </div>
                HTML;
            }
        })
        ->pause(250)
        ->assertScript("document.querySelector('[dusk=implicit]').textContent", '')
        ->assertScript("document.querySelector('[dusk=explicit]').textContent", '')
        ->assertSeeIn('@call', 'Call foo')
        ->assertSeeIn('@count', '0')
        ->assertConsoleLogHasWarning('Cannot call server method')
        ->waitForLivewire()->click('@call')
        ->assertSeeIn('@count', '1');
    }

    public function test_wire_text_can_call_client_side_functions()
    {
        Livewire::visit(new class extends Component {
            public $text = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div x-data="{ uppercase(value) { return value.toUpperCase() } }">
                    <div wire:text="uppercase(text)" dusk="label"></div>
                    <button wire:click="$set('text', 'bar')" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@label', 'FOO')
        ->waitForLivewire()->click('@change')
        ->assertSeeIn('@label', 'BAR');
    }
}
