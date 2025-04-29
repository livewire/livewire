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
            ->waitForLivewireToLoad()
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
            ->waitForLivewireToLoad()
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
    // https://github.com/livewire/livewire/discussions/5935#discussioncomment-11265936
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
    // https://github.com/livewire/livewire/discussions/8800
    // https://github.com/livewire/livewire/discussions/5935#discussioncomment-7091189
    // https://github.com/livewire/livewire/discussions/8928
    // https://github.com/livewire/livewire/discussions/8658
    // https://github.com/livewire/livewire/discussions/8575
    // https://github.com/livewire/livewire/discussions/6698
    // https://github.com/livewire/livewire/discussions/6698#discussioncomment-7432437
    // https://github.com/livewire/livewire/discussions/7193
    // https://github.com/livewire/livewire/discussions/5802
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

    // https://github.com/livewire/livewire/discussions/7697
    public function test_scenario_3_keys_on_nested_in_div_failing_3_using_loop_index_in_key()
    {
        Livewire::visit([
            new class () extends Component {
                public $changeNumbers = false;

                #[\Livewire\Attributes\Computed]
                public function numbers()
                {
                    if ($this->changeNumbers) {
                        return [2,1,4,3];
                    }
                    return [1,2,3,4];
                }
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$toggle('changeNumbers')" dusk="changeNumbers">Change numbers</button>
                        <div>
                            @foreach ($this->numbers as $index => $number)
                                <div wire:key="{{ $number }}">
                                    <livewire:child :$number :key="$index" />
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
            ->assertSee('3')
            ->assertSee('4')
            ->waitForLivewire()->click('@changeNumbers')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('4')
            ->assertSee('3');
    }

    // https://github.com/livewire/livewire/discussions/7282
    public function test_scenario_4_conditionally_removed_elements_passing()
    {
        Livewire::visit([
            new class () extends Component {
                public bool $showContents = false;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div x-init="$el.remove()">
                            {{ __('Javascript required') }}
                        </div>
                        <button wire:click="$toggle('showContents')" dusk="showContents">Show/Hide</button>
                        <div x-show="$wire.showContents" wire:key="container">
                            <livewire:child wire:key="test" />
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>contents</div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertDontSee('contents')
            ->waitForLivewire()->click('@showContents')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('contents');
    }

    // https://github.com/livewire/livewire/discussions/7282
    public function test_scenario_4_conditionally_removed_elements_failing_missing_key_on_wrapping_div()
    {
        Livewire::visit([
            new class () extends Component {
                public bool $showContents = false;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div x-init="$el.remove()">
                            {{ __('Javascript required') }}
                        </div>
                        <button wire:click="$toggle('showContents')" dusk="showContents">Show/Hide</button>
                        <div x-show="$wire.showContents">
                            <livewire:child wire:key="test" />
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>contents</div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertDontSee('contents')
            ->waitForLivewire()->click('@showContents')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('contents');
    }
}
