<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class RoutePrefixBrowserTest extends \Tests\BrowserTestCase
{
    public function test_component_works_with_custom_route_prefix()
    {
        config()->set('livewire.route_prefix', 'legacy');

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
                    <h1>Counter: <span dusk="count">{{ $count }}</span></h1>
                    <button wire:click="increment" dusk="increment">Increment</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '2');
    }

    public function test_component_works_with_nested_route_prefix()
    {
        config()->set('livewire.route_prefix', 'api/v1');

        Livewire::visit(new class extends Component {
            public $message = 'initial';

            public function updateMessage()
            {
                $this->message = 'updated';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <p dusk="message">{{ $message }}</p>
                    <button wire:click="updateMessage" dusk="update">Update</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@message', 'initial')
        ->waitForLivewire()->click('@update')
        ->assertSeeIn('@message', 'updated');
    }

    public function test_wire_model_works_with_custom_prefix()
    {
        config()->set('livewire.route_prefix', 'prefixed');

        Livewire::visit(new class extends Component {
            public $name = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input wire:model.live="name" dusk="input" type="text">
                    <p dusk="output">Hello {{ $name }}</p>
                </div>
                HTML;
            }
        })
        ->assertSee('Hello')
        ->type('@input', 'World')
        ->waitForLivewire()
        ->assertSeeIn('@output', 'Hello World');
    }

    public function test_multiple_components_work_with_custom_prefix()
    {
        config()->set('livewire.route_prefix', 'custom');

        Livewire::visit([
            'counter-a' => new class extends Component {
                public $count = 0;
                public function increment() { $this->count++; }
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="increment" dusk="btn-a">A: {{ $count }}</button>
                    </div>
                    HTML;
                }
            },
            'counter-b' => new class extends Component {
                public $count = 10;
                public function increment() { $this->count++; }
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="increment" dusk="btn-b">B: {{ $count }}</button>
                    </div>
                    HTML;
                }
            },
        ])
        ->assertSee('A: 0')
        ->assertSee('B: 10')
        ->waitForLivewire()->click('@btn-a')
        ->assertSee('A: 1')
        ->assertSee('B: 10')
        ->waitForLivewire()->click('@btn-b')
        ->assertSee('A: 1')
        ->assertSee('B: 11');
    }

    public function test_javascript_receives_correct_prefixed_update_uri()
    {
        config()->set('livewire.route_prefix', 'test-prefix');

        Livewire::visit(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <p>Test Component</p>
                </div>
                HTML;
            }
        })
        ->script([
            // Check that the Livewire JS config has the correct update URI
            'return window.Livewire.all()[0].$wire.__instance.updateUri'
        ], function ($updateUri) {
            $expectedPrefix = '/test-prefix/livewire-';
            $this->assertStringStartsWith($expectedPrefix, $updateUri);
        });
    }

    public function test_component_with_no_prefix_works_normally()
    {
        config()->set('livewire.route_prefix', '');

        Livewire::visit(new class extends Component {
            public $value = 'test';

            public function change()
            {
                $this->value = 'changed';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="value">{{ $value }}</span>
                    <button wire:click="change" dusk="change">Change</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@value', 'test')
        ->waitForLivewire()->click('@change')
        ->assertSeeIn('@value', 'changed');
    }

    public function test_deeply_nested_prefix_works_with_components()
    {
        config()->set('livewire.route_prefix', 'api/v2/admin');

        Livewire::visit(new class extends Component {
            public $active = false;

            public function toggle()
            {
                $this->active = !$this->active;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <p dusk="status">{{ $active ? 'Active' : 'Inactive' }}</p>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@status', 'Inactive')
        ->waitForLivewire()->click('@toggle')
        ->assertSeeIn('@status', 'Active')
        ->waitForLivewire()->click('@toggle')
        ->assertSeeIn('@status', 'Inactive');
    }
}