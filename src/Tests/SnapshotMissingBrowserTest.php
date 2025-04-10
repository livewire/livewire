<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class SnapshotMissingBrowserTest extends \Tests\BrowserTestCase
{
    // https://github.com/livewire/livewire/discussions/9037
    public function test_scenario_1_different_root_element_with_lazy_passing()
    {
        Livewire::visit([
            new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>child</div>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->waitForText('child')
            ->assertSee('child')
            ->assertConsoleLogHasNoErrors();
    }

    // https://github.com/livewire/livewire/discussions/9037
    public function test_scenario_1_different_root_element_with_lazy_failing()
    {
        Livewire::visit([
            new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <section>child</section>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->waitForText('child')
            ->assertSee('child')
            ->assertConsoleLogHasNoErrors();
    }

    // https://github.com/livewire/livewire/discussions/8921
    public function test_scenario_2_keys_in_groups_passing()
    {
        Livewire::visit([
            new #[\Livewire\Attributes\On('number-updated')] class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1>Section 1</h1>
                        <div>
                            @foreach (range(1, 2) as $number)
                                <livewire:child section="1" :$number :key="$number" />
                            @endforeach
                        </div>

                        <h1>Section 2</h1>
                        <div>
                            @foreach (range(3, 4) as $number)
                                <livewire:child section="2" :$number :key="$number" />
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $section;
                public $number;
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $section }}-{{ $number }} - <button type="button" wire:click="$dispatch('number-updated')" dusk="{{ $section }}-{{ $number }}-button">Change number</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1-1')
            ->assertSee('1-2')
            ->assertSee('2-3')
            ->assertSee('2-4')
            ->waitForLivewire()->click('@1-1-button')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('1-1')
            ->assertSee('1-2')
            ->assertSee('2-3')
            ->assertSee('2-4');
    }

    // https://github.com/livewire/livewire/discussions/8921
    public function test_scenario_2_keys_in_groups_failing()
    {
        Livewire::visit([
            new #[\Livewire\Attributes\On('number-updated')] class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1>Section 1</h1>
                        <div>
                            @foreach (range(1, 2) as $number)
                                <livewire:child section="1" :$number :key="$number" />
                            @endforeach
                        </div>

                        <h1>Section 2</h1>
                        <div>
                            @foreach (range(1, 2) as $number)
                                <livewire:child section="2" :$number :key="$number" />
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $section;
                public $number;
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $section }}-{{ $number }} - <button type="button" wire:click="$dispatch('number-updated')" dusk="{{ $section }}-{{ $number }}-button">Change number</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1-1')
            ->assertSee('1-2')
            ->assertSee('2-1')
            ->assertSee('2-2')
            ->waitForLivewire()->click('@1-1-button')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('1-1')
            ->assertSee('1-2')
            ->assertSee('2-1')
            ->assertSee('2-2');
    }

    // https://github.com/livewire/livewire/discussions/8877
    public function test_scenario_3_keys_on_nested_in_div_passing()
    {
        Livewire::visit([
            new class () extends Component {
                public $prepend = false;

                #[\Livewire\Attributes\Computed]
                public function numbers()
                {
                    if ($this->prepend) {
                        return range(0, 2);
                    }

                    return range(1, 2);
                }
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$toggle('prepend')" dusk="prepend">Prepend</button>
                        <div>
                            @foreach ($this->numbers as $index => $number)
                                <div wire:key="{{ $number }}">
                                    <livewire:child :$number :key="$number" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $number;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $number }}
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1')
            ->assertSee('2')
            ->waitForLivewire()->click('@prepend')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('0')
            ->assertSee('1')
            ->assertSee('2');
    }

    // https://github.com/livewire/livewire/discussions/8877
    public function test_scenario_3_keys_on_nested_in_div_failing_1_no_key_on_nested_component()
    {
        Livewire::visit([
            new class () extends Component {
                public $prepend = false;

                #[\Livewire\Attributes\Computed]
                public function numbers()
                {
                    if ($this->prepend) {
                        return range(0, 2);
                    }

                    return range(1, 2);
                }
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$toggle('prepend')" dusk="prepend">Prepend</button>
                        <div>
                            @foreach ($this->numbers as $index => $number)
                                <div wire:key="{{ $number }}">
                                    <livewire:child :$number />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $number;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $number }}
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1')
            ->assertSee('2')
            ->waitForLivewire()->click('@prepend')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('0')
            ->assertSee('1')
            ->assertSee('2');
    }

    // https://github.com/livewire/livewire/discussions/8877
    public function test_scenario_3_keys_on_nested_in_div_failing_2_no_key_on_loop_root_element()
    {
        Livewire::visit([
            new class () extends Component {
                public $prepend = false;

                #[\Livewire\Attributes\Computed]
                public function numbers()
                {
                    if ($this->prepend) {
                        return range(0, 2);
                    }

                    return range(1, 2);
                }
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$toggle('prepend')" dusk="prepend">Prepend</button>
                        <div>
                            @foreach ($this->numbers as $index => $number)
                                <div>
                                    <livewire:child :$number :key="$number" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $number;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $number }}
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1')
            ->assertSee('2')
            ->waitForLivewire()->click('@prepend')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('0')
            ->assertSee('1')
            ->assertSee('2');
    }

    // https://github.com/livewire/livewire/discussions/8921
    public function test_scenario_3_keys_on_nested_in_div_failing_3_incorrect_key_on_loop_root_element()
    {
        Livewire::visit([
            new class () extends Component {
                public $prepend = false;

                #[\Livewire\Attributes\Computed]
                public function numbers()
                {
                    if ($this->prepend) {
                        return range(0, 2);
                    }

                    return range(1, 2);
                }
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$toggle('prepend')" dusk="prepend">Prepend</button>
                        <div>
                            @foreach ($this->numbers as $index => $number)
                                <div wire:key="number">
                                    <livewire:child :$number :key="$number" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $number;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        {{ $number }}
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('1')
            ->assertSee('2')
            ->waitForLivewire()->click('@prepend')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('0')
            ->assertSee('1')
            ->assertSee('2');
    }
}
