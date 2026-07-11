<?php

namespace Livewire\Features\SupportWireIf;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_if_toggles_content_in_and_out_of_the_dom()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>

                    <template wire:if="show">
                        <div dusk="content">Hello</div>
                    </template>
                </div>
                HTML;
            }
        })
        ->assertNotPresent('@content')
        ->assertDontSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertPresent('@content')
        ->assertSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertNotPresent('@content')
        ->assertDontSee('Hello');
    }

    public function test_wire_if_renders_content_on_page_load_when_property_is_initially_true()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <template wire:if="show">
                        <div dusk="content">Hello</div>
                    </template>
                </div>
                HTML;
            }
        })
        ->assertPresent('@content')
        ->assertSee('Hello');
    }

    public function test_wire_if_supports_the_not_operator()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>

                    <template wire:if="! show">
                        <div dusk="content">Hello</div>
                    </template>
                </div>
                HTML;
            }
        })
        ->assertPresent('@content')
        ->waitForLivewire()->click('@toggle')
        ->assertNotPresent('@content');
    }

    public function test_wire_if_reacts_to_client_side_state_changes_without_a_request()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button x-on:click="$wire.show = ! $wire.show" dusk="toggle">Toggle</button>

                    <template wire:if="show">
                        <div dusk="content">Hello</div>
                    </template>
                </div>
                HTML;
            }
        })
        ->assertNotPresent('@content')
        ->click('@toggle')
        ->assertPresent('@content')
        ->click('@toggle')
        ->assertNotPresent('@content');
    }

    public function test_wire_if_content_survives_unrelated_livewire_updates()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <template wire:if="show">
                        <div dusk="content">
                            <input type="text" dusk="input">
                        </div>
                    </template>

                    <p dusk="after">After</p>
                </div>
                HTML;
            }
        })
        ->assertPresent('@content')
        ->type('@input', 'preserve me')
        ->waitForLivewire()->click('@refresh')
        // The generated content isn't in the server-rendered HTML, so morphing
        // must skip over it instead of removing it or diffing it against the
        // "After" paragraph. The typed input value proves the element also
        // wasn't torn down and recreated...
        ->assertPresent('@content')
        ->assertValue('@input', 'preserve me')
        ->assertSeeIn('@after', 'After');
    }

    public function test_wire_if_content_can_contain_livewire_directives()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            public $count = 0;

            public function increment() { $this->count++; }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <template wire:if="show">
                        <div>
                            <button wire:click="increment" dusk="increment">Increment</button>

                            <span wire:text="count" dusk="count"></span>
                        </div>
                    </template>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1');
    }
}
