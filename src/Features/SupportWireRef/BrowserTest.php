<?php

namespace Livewire\Features\SupportWireRef;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_stream_to_a_ref()
    {
        Livewire::visit([
            new class extends Component {
                public $streamed = false;

                public function send()
                {
                    $this->stream('testing...', ref: 'target');

                    $this->streamed = true;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div wire:ref="target" dusk="target">{{ $streamed ? 'testing...' : '' }}</div>

                        <button wire:click="send" dusk="send-button">Send</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@send-button')
            // Wait for children to update...
            ->pause(50)
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@target', 'testing...');
    }

    public function test_can_dispatch_an_event_to_a_ref()
    {
        Livewire::visit([
            new class extends Component {
                public function send()
                {
                    $this->dispatch('test', ref: 'child2');
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child1" name="child1" />
                        <livewire:child wire:ref="child2" name="child2" />
                        <button wire:click="send" dusk="send-button">Send</button>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $received = false;
                public $name;

                #[On('test')]
                public function test()
                {
                    $this->received = true;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $name }}
                        <p dusk="{{ $name }}-received-output">{{ $received ? 'true' : 'false' }}</p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@send-button')
            // Wait for children to update...
            ->pause(50)
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@child1-received-output', 'false')
            ->assertSeeIn('@child2-received-output', 'true');
    }

    public function test_a_dispatch_to_a_non_existent_ref_logs_an_error_in_the_console()
    {
        Livewire::visit([
            new class extends Component {
                public function send()
                {
                    $this->dispatch('test', ref: 'child9999');
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child1" name="child1" />
                        <livewire:child wire:ref="child2" name="child2" />
                        <button wire:click="send" dusk="send-button">Send</button>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $received = false;
                public $name;

                #[On('test')]
                public function test()
                {
                    $this->received = true;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $name }}
                        <p dusk="{{ $name }}-received-output">{{ $received ? 'true' : 'false' }}</p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@send-button')
            // Wait for children to update...
            ->pause(50)
            ->assertConsoleLogHasErrors()
            ->assertSeeIn('@child1-received-output', 'false')
            ->assertSeeIn('@child2-received-output', 'false');
    }

    public function test_can_use_dispatch_ref_magic_to_dispatch_an_event_to_a_ref()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child1" name="child1" />
                        <livewire:child wire:ref="child2" name="child2" />
                        <button wire:click="$refs['child1'].$wire.dispatchSelf('test')" dusk="send-button">Send</button>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $received = false;
                public $name;

                #[On('test')]
                public function test()
                {
                    $this->received = true;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $name }}
                        <p dusk="{{ $name }}-received-output">{{ $received ? 'true' : 'false' }}</p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@send-button')
            // Wait for children to update...
            ->pause(50)
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@child1-received-output', 'true')
            ->assertSeeIn('@child2-received-output', 'false');
    }

    public function test_use_refs_magic_to_get_nested_component()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child" />
                        <p wire:text="$refs.child.textContent" dusk="child-ref-output"></p>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        Child
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@child-ref-output', 'Child');
    }

    public function test_refs_magic_logs_an_error_in_the_console_if_the_ref_is_not_a_component_but_is_used_as_one()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div wire:ref="child"></div>
                        <p wire:text="$refs.child.save()" dusk="child-ref-output"></p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertConsoleLogHasErrors()
            ;
    }

    public function test_refs_magic_logs_an_error_in_the_console_if_the_ref_is_not_found()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <p wire:text="$refs.child?.textContent" dusk="child-ref-output"></p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertConsoleLogHasErrors()
            ;
    }
}