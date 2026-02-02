<?php

namespace Livewire\Features\SupportDataBinding;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BrowserTest extends BrowserTestCase
{
    function test_can_use_wire_dirty()
    {
        Livewire::visit(new class extends Component {
            public $prop = false;

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="checkbox" type="checkbox" wire:model="prop" value="true"  />

                        <div wire:dirty>Unsaved changes...</div>
                        <div wire:dirty.remove>The data is in-sync...</div>
                    </div>
                BLADE;
            }
        })
            ->assertSee('The data is in-sync...')
            ->check('@checkbox')
            ->pause(50)
            ->assertDontSee('The data is in-sync')
            ->assertSee('Unsaved changes...')
            ->uncheck('@checkbox')
            ->assertSee('The data is in-sync...')
            ->assertDontSee('Unsaved changes...')
        ;
    }

    function test_can_use_dollar_dirty_to_check_if_component_is_dirty()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />

                        <div x-show="$wire.$dirty()" dusk="dirty-indicator">Component is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty-indicator')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty-indicator');
        ;
    }

    function test_can_use_dollar_dirty_to_check_if_specific_property_is_dirty()
    {
        Livewire::visit(new class extends Component {
            public $title = '';
            public $description = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title" type="text" wire:model="title" />
                        <input dusk="description" type="text" wire:model="description" />

                        <div x-show="$wire.$dirty('title')" dusk="title-dirty">Title is dirty</div>
                        <div x-show="$wire.$dirty('description')" dusk="description-dirty">Description is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@title-dirty')
            ->assertNotVisible('@description-dirty')
            ->type('@title', 'Hello')
            ->pause(50)
            ->assertVisible('@title-dirty')
            ->assertNotVisible('@description-dirty')
            ->type('@description', 'World')
            ->pause(50)
            ->assertVisible('@title-dirty')
            ->assertVisible('@description-dirty')
        ;
    }

    function test_dollar_dirty_clears_after_network_request()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />

                        <button dusk="commit" type="button" wire:click="$commit">Commit</button>

                        <div x-show="$wire.$dirty()" dusk="dirty-indicator">Component is dirty</div>
                    </div>
                BLADE;
            }
        })
            ->assertNotVisible('@dirty-indicator')
            ->type('@input', 'Hello')
            ->pause(50)
            ->assertVisible('@dirty-indicator')
            ->waitForLivewire()->click('@commit')
            ->pause(50)
            ->assertNotVisible('@dirty-indicator')
        ;
    }

    function test_can_update_bound_value_from_lifecyle_hook()
    {
        Livewire::visit(new class extends Component {
            public $foo = null;

            public $bar = null;

            public function updatedFoo(): void
            {
                $this->bar = null;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <select wire:model.live="foo" dusk="fooSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>

                        <select wire:model="bar" dusk="barSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>
                    </div>
                BLADE;
            }
        })
            ->select('@barSelect', 'one')
            ->waitForLivewire()->select('@fooSelect', 'one')
            ->assertSelected('@barSelect', '')
        ;
    }

    public function updates_dependent_select_options_correctly_when_wire_key_is_applied()
    {
        Livewire::visit(new class extends Component {
            public $parent = 'foo';

            public $child = 'bar';

            protected $options = [
                'foo' => [
                    'bar',
                ],
                'baz' => [
                    'qux',
                ],
            ];

            #[Computed]
            public function parentOptions(): array
            {
                return array_keys($this->options);
            }

            #[Computed]
            public function childOptions(): array
            {
                return $this->options[$this->parent];
            }

            public function render(): string
            {
                return <<<'blade'
                    <div>
                        <select wire:model.live="parent" dusk="parent">
                            @foreach($this->parentOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>

                        <select wire:model="child" dusk="child" wire:key="{{ $parent }}">
                            <option value>Select</option>
                            @foreach($this->childOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                blade;
            }
        })
            ->waitForLivewire()->select('@parent', 'baz')
            ->assertSelected('@child', '')
            ->waitForLivewire()->select('@parent', 'foo')
            ->assertSelected('@child', 'bar');
    }

    public function test_multiple_wire_set_calls_to_empty_string_are_all_sent_to_server()
    {
        Livewire::visit(new class extends Component {
            public array $parent = [
                'foo' => 'bar',
                'baz' => 'qux',
            ];

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <span dusk="foo">{{ $parent['foo'] }}</span>
                        <span dusk="baz">{{ $parent['baz'] }}</span>

                        <button
                            dusk="clear-both"
                            type="button"
                            x-on:click="$wire.set('parent.foo', ''); $wire.set('parent.baz', ''); $wire.commit()"
                        >
                            Clear Both
                        </button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@foo', 'bar')
            ->assertSeeIn('@baz', 'qux')
            ->waitForLivewire()->click('@clear-both')
            ->assertDontSeeIn('@foo', 'bar')
            ->assertDontSeeIn('@baz', 'qux')
        ;
    }

    public function test_wire_model_ephemeral_syncs_immediately_no_network()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
        ;
    }

    public function test_wire_model_blur_delays_ephemeral_sync_no_network()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.blur="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->click('@blur-target')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
        ;
    }

    public function test_wire_model_change_delays_ephemeral_sync_no_network()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.change.live="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->click('@blur-target')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
        ;
    }

    public function test_wire_model_enter_delays_ephemeral_sync_no_network()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.enter="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->click('@blur-target')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->click('@input')
            ->keys('@input', '{enter}')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
        ;
    }

    public function test_wire_model_blur_enter_delays_ephemeral_sync_until_blur_or_enter()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.blur.enter="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->type('@input', 'by-enter')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->keys('@input', '{enter}')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'by-enter')
            ->assertSeeNothingIn('@server')
            ->clear('@input')
            ->type('@input', 'by-blur')
            ->pause(50)
            ->click('@blur-target')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'by-blur')
            ->assertSeeNothingIn('@server')
        ;
    }

    public function test_wire_model_live_ephemeral_immediate_network_debounced()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.live="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->waitForTextIn('@server', 'hello')
        ;
    }

    public function test_wire_model_live_blur_ephemeral_immediate_network_on_blur()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.live.blur="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
            ->waitForLivewire()->click('@blur-target')
            ->assertSeeIn('@server', 'hello')
        ;
    }

    public function test_wire_model_blur_live_ephemeral_on_blur_network_on_blur()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.blur.live="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->waitForLivewire()->click('@blur-target')
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeIn('@server', 'hello')
        ;
    }

    public function test_wire_model_blur_live_debounce_ephemeral_on_blur_network_debounced()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.blur.live.debounce.300ms="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->click('@blur-target')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
            ->waitForTextIn('@server', 'hello')
        ;
    }

    public function test_wire_model_live_enter_ephemeral_immediate_network_on_enter()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model.live.enter="title" />
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <button dusk="blur-target">Blur Target</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->type('@input', 'hello')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'hello')
            ->assertSeeNothingIn('@server')
            ->click('@blur-target')
            ->pause(200)
            ->assertSeeNothingIn('@server')
            ->click('@input')
            ->waitForLivewire()->keys('@input', '{enter}')
            ->assertSeeIn('@server', 'hello')
        ;
    }

    function test_wire_model_blur_syncs_value_on_form_submit_via_enter()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public $submitted = false;

            public function submit()
            {
                $this->submitted = true;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <form wire:submit="submit">
                            <input dusk="input" type="text" wire:model.blur="title" />
                            <button dusk="submit" type="submit">Submit</button>
                        </form>
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <span dusk="submitted">{{ $submitted ? 'yes' : 'no' }}</span>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->assertSeeIn('@submitted', 'no')
            ->type('@input', 'hello')
            ->pause(50)
            // Value hasn't synced yet (blur hasn't fired)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            // Press Enter to submit the form from inside the input
            ->waitForLivewire()->keys('@input', '{enter}')
            // The value should have been synced before the submit payload was built
            ->assertSeeIn('@submitted', 'yes')
            ->assertSeeIn('@server', 'hello')
        ;
    }

    function test_wire_model_change_syncs_value_on_form_submit_via_enter()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public $submitted = false;

            public function submit()
            {
                $this->submitted = true;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <form wire:submit="submit">
                            <input dusk="input" type="text" wire:model.change="title" />
                            <button dusk="submit" type="submit">Submit</button>
                        </form>
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <span dusk="submitted">{{ $submitted ? 'yes' : 'no' }}</span>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->assertSeeIn('@submitted', 'no')
            ->type('@input', 'hello')
            ->pause(50)
            // Value hasn't synced yet (change hasn't fired)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            // Press Enter to submit the form from inside the input
            ->waitForLivewire()->keys('@input', '{enter}')
            // The value should have been synced before the submit payload was built
            ->assertSeeIn('@submitted', 'yes')
            ->assertSeeIn('@server', 'hello')
        ;
    }

    function test_wire_model_enter_syncs_value_on_form_submit_via_enter()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public $submitted = false;

            public function submit()
            {
                $this->submitted = true;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <form wire:submit="submit">
                            <input dusk="input" type="text" wire:model.enter="title" />
                            <button dusk="submit" type="submit">Submit</button>
                        </form>
                        <span dusk="ephemeral" x-text="$wire.title"></span>
                        <span dusk="server">{{ $title }}</span>
                        <span dusk="submitted">{{ $submitted ? 'yes' : 'no' }}</span>
                    </div>
                BLADE;
            }
        })
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            ->assertSeeIn('@submitted', 'no')
            ->type('@input', 'hello')
            ->pause(50)
            // Value hasn't synced yet (enter hasn't been pressed)
            ->assertSeeNothingIn('@ephemeral')
            ->assertSeeNothingIn('@server')
            // Press Enter to submit the form from inside the input
            ->waitForLivewire()->keys('@input', '{enter}')
            // The value should have been synced before the submit payload was built
            ->assertSeeIn('@submitted', 'yes')
            ->assertSeeIn('@server', 'hello')
        ;
    }
}
