<?php

namespace Livewire\V4\Refs;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_use_ref_magic_as_a_function_to_get_nested_component()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child" />
                        <p wire:text="$ref('child').el.textContent" dusk="child-ref-output"></p>
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

    public function test_use_ref_magic_as_a_property_to_get_nested_component()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child" />
                        <p wire:text="$ref.child.el.textContent" dusk="child-ref-output"></p>
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

    public function test_use_refs_plural_magic_as_a_function_as_an_alias_for_ref_magic()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child" />
                        <p wire:text="$refs('child').el.textContent" dusk="child-ref-output"></p>
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

    public function test_use_refs_plural_magic_as_a_property_as_an_alias_for_ref_magic()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child wire:ref="child" />
                        <p wire:text="$refs.child.el.textContent" dusk="child-ref-output"></p>
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

    public function test_ref_magic_logs_an_error_in_the_console_if_the_ref_is_not_found()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <p wire:text="$ref.child?.el?.textContent" dusk="child-ref-output"></p>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertConsoleLogHasErrors()
            ;
    }

    public function test_can_dispatch_an_event_to_a_ref()
    {
        Livewire::visit([
            new class extends Component {
                public function send()
                {
                    $this->dispatch('test')->to(ref: 'child2');
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
                    $this->dispatch('test')->to(ref: 'child9999');
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
                        <button wire:click="$dispatchRef('child1', 'test')" dusk="send-button">Send</button>
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
}