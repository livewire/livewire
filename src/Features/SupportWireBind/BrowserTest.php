<?php

namespace Livewire\Features\SupportWireBind;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_bind_binds_attribute_on_init()
    {
        Livewire::visit(new class extends Component {
            public $class = 'text-red';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:bind:class="class" dusk="target">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertAttribute('@target', 'class', 'text-red');
    }

    public function test_wire_bind_updates_attribute_when_property_changes()
    {
        Livewire::visit(new class extends Component {
            public $class = 'text-red';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:bind:class="class" dusk="target">Hello</div>
                    <button wire:click="$set('class', 'text-blue')" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertAttribute('@target', 'class', 'text-red')
        ->waitForLivewire()->click('@change')
        ->assertAttribute('@target', 'class', 'text-blue');
    }

    public function test_wire_bind_can_bind_style_attribute()
    {
        Livewire::visit(new class extends Component {
            public $color = 'red';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:bind:style="{ 'color': color }" dusk="target">Hello</div>
                    <button wire:click="$set('color', 'blue')" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertAttribute('@target', 'style', 'color: red;')
        ->waitForLivewire()->click('@change')
        ->assertAttribute('@target', 'style', 'color: blue;');
    }

    public function test_wire_bind_can_bind_href_attribute()
    {
        Livewire::visit(new class extends Component {
            public $url = 'https://example.com';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <a wire:bind:href="url" dusk="link">Link</a>
                    <button wire:click="$set('url', 'https://livewire.dev')" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertAttribute('@link', 'href', 'https://example.com/')
        ->waitForLivewire()->click('@change')
        ->assertAttribute('@link', 'href', 'https://livewire.dev/');
    }

    public function test_wire_bind_can_bind_disabled_attribute()
    {
        Livewire::visit(new class extends Component {
            public $disabled = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:bind:disabled="disabled" dusk="target">Submit</button>
                    <button wire:click="$toggle('disabled')" dusk="toggle">Toggle</button>
                </div>
                HTML;
            }
        })
        ->assertAttribute('@target', 'disabled', 'true')
        ->waitForLivewire()->click('@toggle')
        ->assertAttributeMissing('@target', 'disabled');
    }
}
