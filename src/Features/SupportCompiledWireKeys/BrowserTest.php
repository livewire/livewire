<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('livewire.smart_wire_keys', true);
    }

    public function test_nested_components_with_nested_and_sibling_loops_all_work_without_keys()
    {
        Livewire::visit([
            new class () extends Component {
                public $items = ['B', 'D'];

                public function prepend() {
                    $this->items = ['A','B','D'];
                }

                public function insert() {
                    $this->items = ['B','C','D'];
                }

                public function append() {
                    $this->items = ['B','D','E'];
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="prepend" dusk="prepend">Prepend</button>
                        <button wire:click="insert" dusk="insert">Insert</button>
                        <button wire:click="append" dusk="append">Append</button>
                        <div>
                            @foreach ($items as $item)
                                <div wire:key="{{ $item }}">
                                    @foreach ($items as $item2)
                                        <div wire:key="{{ $item2 }}">
                                            <livewire:child :item="'loop-1-' . $item . $item2" dusk="child-loop-1-{{ $item }}-{{ $item2 }}" />
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            @foreach ($items as $item)
                                <div wire:key="{{ $item }}">
                                    @foreach ($items as $item2)
                                        <div wire:key="{{ $item2 }}">
                                            <livewire:child :item="'loop-2-' . $item . $item2" dusk="child-loop-2-{{ $item }}-{{ $item2 }}" />
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $item;

                public $other;

                public function render()
                {
                    return <<<'HTML'
                    <div>Child: {{ $item }} <input wire:model="other" dusk="child-{{$item}}-input" /></div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertSee('Child: loop-1-BB')
            ->assertSee('Child: loop-1-BD')
            ->assertSee('Child: loop-1-DB')
            ->assertSee('Child: loop-1-DD')
            ->assertSee('Child: loop-2-BB')
            ->assertSee('Child: loop-2-BD')
            ->assertSee('Child: loop-2-DB')
            ->assertSee('Child: loop-2-DD')

            // Input some values to make sure state is retained...
            ->type('@child-loop-1-BB-input', '1bb')
            ->type('@child-loop-1-BD-input', '1bd')
            ->type('@child-loop-1-DB-input', '1db')
            ->type('@child-loop-1-DD-input', '1dd')
            ->type('@child-loop-2-BB-input', '2bb')
            ->type('@child-loop-2-BD-input', '2bd')
            ->type('@child-loop-2-DB-input', '2db')
            ->type('@child-loop-2-DD-input', '2dd')

            // Test prepending...
            ->waitForLivewire()->click('@prepend')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('Child: loop-1-AA')
            ->assertSee('Child: loop-1-AB')
            ->assertSee('Child: loop-1-AD')
            ->assertSee('Child: loop-1-BA')
            ->assertSee('Child: loop-1-BB')
            ->assertSee('Child: loop-1-BD')
            ->assertSee('Child: loop-1-DA')
            ->assertSee('Child: loop-1-DB')
            ->assertSee('Child: loop-1-DD')
            ->assertSee('Child: loop-2-AA')
            ->assertSee('Child: loop-2-AB')
            ->assertSee('Child: loop-2-AD')
            ->assertSee('Child: loop-2-BA')
            ->assertSee('Child: loop-2-BB')
            ->assertSee('Child: loop-2-BD')
            ->assertSee('Child: loop-2-DA')
            ->assertSee('Child: loop-2-DB')
            ->assertSee('Child: loop-2-DD')
            ->assertValue('@child-loop-1-AA-input', '')
            ->assertValue('@child-loop-1-AB-input', '')
            ->assertValue('@child-loop-1-AD-input', '')
            ->assertValue('@child-loop-1-BA-input', '')
            ->assertValue('@child-loop-1-BB-input', '1bb')
            ->assertValue('@child-loop-1-BD-input', '1bd')
            ->assertValue('@child-loop-1-DA-input', '')
            ->assertValue('@child-loop-1-DB-input', '1db')
            ->assertValue('@child-loop-1-DD-input', '1dd')
            ->assertValue('@child-loop-2-AA-input', '')
            ->assertValue('@child-loop-2-AB-input', '')
            ->assertValue('@child-loop-2-AD-input', '')
            ->assertValue('@child-loop-2-BA-input', '')
            ->assertValue('@child-loop-2-BB-input', '2bb')
            ->assertValue('@child-loop-2-BD-input', '2bd')
            ->assertValue('@child-loop-2-DA-input', '')
            ->assertValue('@child-loop-2-DB-input', '2db')
            ->assertValue('@child-loop-2-DD-input', '2dd')

            // Test inserting...
            ->waitForLivewire()->click('@insert')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('Child: loop-1-BB')
            ->assertSee('Child: loop-1-BC')
            ->assertSee('Child: loop-1-BD')
            ->assertSee('Child: loop-1-CB')
            ->assertSee('Child: loop-1-CC')
            ->assertSee('Child: loop-1-CD')
            ->assertSee('Child: loop-1-DB')
            ->assertSee('Child: loop-1-DC')
            ->assertSee('Child: loop-1-DD')
            ->assertSee('Child: loop-2-BB')
            ->assertSee('Child: loop-2-BC')
            ->assertSee('Child: loop-2-BD')
            ->assertSee('Child: loop-2-CB')
            ->assertSee('Child: loop-2-CC')
            ->assertSee('Child: loop-2-CD')
            ->assertSee('Child: loop-2-DB')
            ->assertSee('Child: loop-2-DC')
            ->assertSee('Child: loop-2-DD')
            ->assertValue('@child-loop-1-BB-input', '1bb')
            ->assertValue('@child-loop-1-BC-input', '')
            ->assertValue('@child-loop-1-BD-input', '1bd')
            ->assertValue('@child-loop-1-CB-input', '')
            ->assertValue('@child-loop-1-CC-input', '')
            ->assertValue('@child-loop-1-CD-input', '')
            ->assertValue('@child-loop-1-DB-input', '1db')
            ->assertValue('@child-loop-1-DC-input', '')
            ->assertValue('@child-loop-1-DD-input', '1dd')
            ->assertValue('@child-loop-2-BB-input', '2bb')
            ->assertValue('@child-loop-2-BC-input', '')
            ->assertValue('@child-loop-2-BD-input', '2bd')
            ->assertValue('@child-loop-2-CB-input', '')
            ->assertValue('@child-loop-2-CC-input', '')
            ->assertValue('@child-loop-2-CD-input', '')
            ->assertValue('@child-loop-2-DB-input', '2db')
            ->assertValue('@child-loop-2-DC-input', '')
            ->assertValue('@child-loop-2-DD-input', '2dd')

            // Test appending...
            ->waitForLivewire()->click('@append')
            ->assertConsoleLogHasNoErrors()
            ->assertSee('Child: loop-1-BB')
            ->assertSee('Child: loop-1-BD')
            ->assertSee('Child: loop-1-BE')
            ->assertSee('Child: loop-1-DB')
            ->assertSee('Child: loop-1-DD')
            ->assertSee('Child: loop-1-DE')
            ->assertSee('Child: loop-1-EB')
            ->assertSee('Child: loop-1-ED')
            ->assertSee('Child: loop-1-EE')
            ->assertSee('Child: loop-2-BB')
            ->assertSee('Child: loop-2-BD')
            ->assertSee('Child: loop-2-BE')
            ->assertSee('Child: loop-2-DB')
            ->assertSee('Child: loop-2-DD')
            ->assertSee('Child: loop-2-DE')
            ->assertSee('Child: loop-2-EB')
            ->assertSee('Child: loop-2-ED')
            ->assertSee('Child: loop-2-EE')
            ->assertValue('@child-loop-1-BB-input', '1bb')
            ->assertValue('@child-loop-1-BD-input', '1bd')
            ->assertValue('@child-loop-1-BE-input', '')
            ->assertValue('@child-loop-1-DB-input', '1db')
            ->assertValue('@child-loop-1-DD-input', '1dd')
            ->assertValue('@child-loop-1-DE-input', '')
            ->assertValue('@child-loop-1-EB-input', '')
            ->assertValue('@child-loop-1-ED-input', '')
            ->assertValue('@child-loop-1-EE-input', '')
            ->assertValue('@child-loop-2-BB-input', '2bb')
            ->assertValue('@child-loop-2-BD-input', '2bd')
            ->assertValue('@child-loop-2-BE-input', '')
            ->assertValue('@child-loop-2-DB-input', '2db')
            ->assertValue('@child-loop-2-DD-input', '2dd')
            ->assertValue('@child-loop-2-DE-input', '')
            ->assertValue('@child-loop-2-EB-input', '')
            ->assertValue('@child-loop-2-ED-input', '')
            ->assertValue('@child-loop-2-EE-input', '')
            ->assertConsoleLogHasNoErrors();
    }

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
                public function placeholder()
                {
                    return '<section></section>';
                }

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
        $this->markTestSkipped('This is skipped because we have decided to not add smart wire keys to nested components if they already have a key. If this scenario keeps being a problem, we can look at fixing this in the future.');

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
