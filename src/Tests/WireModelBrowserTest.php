<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class WireModelBrowserTest extends \Tests\BrowserTestCase
{
    public function test_supports_bracket_notation_in_expressions()
    {
        Livewire::visit(new class extends Component {
            public $foo = [
                'bars' => [
                    'baz' => 'qux',
                ],
            ];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model="foo['bars']['baz']" dusk="input">
                    <span wire:text="foo['bars']['baz']" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire');
    }

    public function test_wire_model_self_by_default_or_with_bubble_modifier()
    {
        Livewire::visit(new class extends Component {
            public $foo = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:model="foo">
                        <input type="text" dusk="default-input">
                    </div>

                    <div wire:model.deep="foo">
                        <input type="text" dusk="bubble-input">
                    </div>

                    <span wire:text="foo" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@default-input', 'livewire', 50)
            ->assertDontSeeIn('@text', 'livewire')
            ->typeSlowly('@bubble-input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire');
    }

    public function test_wire_model_dot_blur()
    {
        Livewire::visit(new class extends Component {
            public $foo = [
                'bars' => [
                    'baz' => 'qux',
                ],
            ];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model="foo['bars']['baz']" dusk="input">
                    <span wire:text="foo['bars']['baz']" dusk="text"></span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire');
    }

    public function test_debounces_requests_on_input_elements_by_default()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(200) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    public function test_debounces_requests_with_custom_duration()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.debounce.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(300) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    public function test_throttles_requests_with_custom_duration()
    {
        Livewire::visit(new class extends Component {
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.throttle.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 40) // 320ms total
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(200) // Wait for the request to be handled
            ->assertScript('window.requests', 2); // Two requests: after 250ms and 500ms
    }
}
