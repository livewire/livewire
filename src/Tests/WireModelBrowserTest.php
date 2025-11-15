<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class WireModelBrowserTest extends \Tests\BrowserTestCase
{
    public function test_debounces_requests_on_input_elements_by_default()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function updated()
            {
                $this->js('window.requests++');
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(200) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    public function test_debounces_requests_with_custom_duration()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function updated()
            {
                $this->js('window.requests++');
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.debounce.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(300) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    public function test_throttles_requests_with_custom_duration()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function updated()
            {
                $this->js('window.requests++');
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.throttle.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->typeSlowly('@input', 'livewire', 50) // 400ms total
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(200) // Wait for the request to be handled
            ->assertScript('window.requests', 2); // Two requests: after 250ms and 500ms
    }
}
