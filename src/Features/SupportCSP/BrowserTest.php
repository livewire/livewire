<?php

namespace Livewire\Features\SupportCSP;

use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['livewire.csp_safe' => true]);
        };
    }

    public function test_basic_counter_component_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function decrement()
            {
                $this->count--;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="count">{{ $count }}</span>
                    <button dusk="increment" wire:click="increment">+</button>
                    <button dusk="decrement" wire:click="decrement">-</button>
                </div>
                HTML;
            }
        })
            ->assertSee('0')
            ->waitForLivewire()->click('@increment')
            ->assertSee('1')
            ->waitForLivewire()->click('@increment')
            ->assertSee('2')
            ->waitForLivewire()->click('@decrement')
            ->assertSee('1')
        ;
    }

    public function test_wire_model_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $message = '';
            public $number = 0;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="message" type="text" wire:model="message" placeholder="Type something...">
                    <span dusk="output">{{ $message }}</span>

                    <input dusk="number" type="number" wire:model="number">
                    <span dusk="number-output">{{ $number }}</span>

                    <button dusk="refresh" wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->type('@message', 'Hello CSP')
            ->waitForLivewire()->click('@refresh')
            ->assertSee('Hello CSP')
            ->type('@number', '42')
            ->waitForLivewire()->click('@refresh')
            ->assertSee('42')
        ;
    }

    public function test_wire_model_live_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="search" type="text" wire:model.live="search" placeholder="Search...">
                    <div dusk="results">Results: {{ strlen($search) }} characters</div>
                </div>
                HTML;
            }
        })
            ->assertSee('Results: 0 characters')
            ->type('@search', 'test')
            ->waitForText('Results: 4 characters')
            ->type('@search', 'testing')
            ->waitForText('Results: 7 characters')
        ;
    }

    public function test_wire_submit_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $name = '';
            public $submitted = false;

            public function submit()
            {
                $this->submitted = true;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <form wire:submit="submit">
                        <input dusk="name" type="text" wire:model="name" placeholder="Enter name">
                        <button dusk="submit" type="submit">Submit</button>
                    </form>

                    @if($submitted)
                        <div dusk="success">Form submitted with: {{ $name }}</div>
                    @endif
                </div>
                HTML;
            }
        })
            ->assertDontSee('Form submitted with:')
            ->type('@name', 'John Doe')
            ->waitForLivewire()->click('@submit')
            ->waitForText('Form submitted with: John Doe')
        ;
    }

    public function test_wire_loading_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public function slowAction()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="slow-button" wire:click="slowAction">Slow Action</button>
                    <div dusk="loading" wire:loading>Loading...</div>
                    <div dusk="loading-target" wire:loading.remove wire:target="slowAction">Ready</div>
                </div>
                HTML;
            }
        })
            ->assertSee('Ready')
            ->assertDontSee('Loading...')
            ->click('@slow-button')
            ->waitForText('Loading...')
            ->assertDontSee('Ready')
            ->waitForText('Ready', 5)
            ->assertDontSee('Loading...')
        ;
    }

    public function test_wire_dirty_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $text = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="text" type="text" wire:model="text">
                    <div dusk="dirty" wire:dirty>Unsaved changes</div>
                    <div dusk="clean" wire:dirty.remove>All saved</div>
                    <button dusk="refresh" wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertSee('All saved')
            ->assertDontSee('Unsaved changes')
            ->type('@text', 'test')
            ->assertSee('Unsaved changes')
            ->assertDontSee('All saved')
            ->waitForLivewire()->click('@refresh')
            ->assertSee('All saved')
            ->assertDontSee('Unsaved changes')
        ;
    }

    public function test_conditional_rendering_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function toggle()
            {
                $this->show = !$this->show;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="toggle" wire:click="toggle">Toggle</button>

                    @if($show)
                        <div dusk="content">This content is visible</div>
                    @else
                        <div dusk="hidden">This content is hidden</div>
                    @endif
                </div>
                HTML;
            }
        })
            ->assertSee('This content is hidden')
            ->assertDontSee('This content is visible')
            ->waitForLivewire()->click('@toggle')
            ->assertSee('This content is visible')
            ->assertDontSee('This content is hidden')
            ->waitForLivewire()->click('@toggle')
            ->assertSee('This content is hidden')
            ->assertDontSee('This content is visible')
        ;
    }

    public function test_multiple_actions_work_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $counter = 0;
            public $message = '';

            public function increment()
            {
                $this->counter++;
            }

            public function updateMessage($text)
            {
                $this->message = $text;
            }

            public function resetProperties()
            {
                $this->counter = 0;
                $this->message = '';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="counter">Count: {{ $counter }}</div>
                    <div dusk="message">Message: {{ $message }}</div>

                    <button dusk="increment" wire:click="increment">Increment</button>
                    <button dusk="set-message" wire:click="updateMessage('Hello World')">Set Message</button>
                    <button dusk="reset" wire:click="resetProperties">Reset</button>
                </div>
                HTML;
            }
        })
            ->assertSee('Count: 0')
            ->assertSee('Message:')
            ->waitForLivewire()->click('@increment')
            ->assertSee('Count: 1')
            ->waitForLivewire()->click('@set-message')
            ->assertSee('Message: Hello World')
            ->waitForLivewire()->click('@increment')
            ->assertSee('Count: 2')
            ->waitForLivewire()->click('@reset')
            ->assertSee('Count: 0')
            ->assertSee('Message:')
        ;
    }

    public function test_wire_keydown_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $pressed = false;

            public function handleEnter()
            {
                $this->pressed = true;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="input" type="text" wire:keydown.enter="handleEnter" placeholder="Press Enter">

                    @if($pressed)
                        <div dusk="pressed">Enter was pressed!</div>
                    @endif
                </div>
                HTML;
            }
        })
            ->assertDontSee('Enter was pressed!')
            ->keys('@input', '{enter}')
            ->waitForText('Enter was pressed!')
        ;
    }

    public function test_wire_model_dot_notation_works_with_csp()
    {
        Livewire::visit(new class extends Component {
            public $user = [
                'name' => '',
                'email' => '',
                'profile' => [
                    'bio' => '',
                    'age' => 0
                ]
            ];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="name" type="text" wire:model="user.name" placeholder="Name">
                    <input dusk="email" type="email" wire:model="user.email" placeholder="Email">
                    <textarea dusk="bio" wire:model="user.profile.bio" placeholder="Bio"></textarea>
                    <input dusk="age" type="number" wire:model="user.profile.age" placeholder="Age">

                    <div dusk="output">
                        <div>Name: {{ $user['name'] }}</div>
                        <div>Email: {{ $user['email'] }}</div>
                        <div>Bio: {{ $user['profile']['bio'] }}</div>
                        <div>Age: {{ $user['profile']['age'] }}</div>
                    </div>

                    <button dusk="refresh" wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->type('@name', 'John Doe')
            ->type('@email', 'john@example.com')
            ->type('@bio', 'Software developer')
            ->type('@age', '30')
            ->waitForLivewire()->click('@refresh')
            ->assertSee('Name: John Doe')
            ->assertSee('Email: john@example.com')
            ->assertSee('Bio: Software developer')
            ->assertSee('Age: 30')
        ;
    }
}