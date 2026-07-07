<?php

namespace Livewire\Features\SupportMorphAwareBladeCompilation;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_blade_conditionals_are_handled_properly_by_morphdom()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            function toggle()
            {
                $this->show = ! $this->show;
            }

            function render() {
                return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    <div>
                        @if ($show)
                            <div dusk="foo">foo</div>
                        @endif

                        <div>bar<input dusk="bar"></div>
                    </div>
                </div>
                HTML;
            }
        })
        ->type('@bar', 'Hey!')
        ->waitForLivewire()->click('@toggle')
        ->assertInputValue('@bar', 'Hey!')
        ->assertNotPresent('@foo')
        ->waitForLivewire()->click('@toggle')
        ->assertInputValue('@bar', 'Hey!')
        ->assertVisible('@foo')
        ;
    }

    public function test_blade_conditional_actions_are_handled_properly_by_morphdom()
    {
        Livewire::visit(new class extends Component {

            public $enabled = true;

            function enable()
            {
                $this->enabled = true;
            }

            function disable()
            {
                $this->enabled = false;
            }

            function render() {
                return <<<'HTML'
                <div>
                    <div>
                        @if ($enabled)
                            <button wire:click="disable" dusk="disable">Disable</button>
                        @else
                            <button wire:click="enable" dusk="enable">Enable</button>
                        @endif
                    </div>
                </div>
                HTML;
            }
        })
        ->waitForLivewire()->click('@disable')
        ->assertNotPresent('@disable')
        ->assertVisible('@enable')
        ->waitForLivewire()->click('@enable')
        ->assertNotPresent('@enable')
        ->assertVisible('@disable')
        ;
    }

    public function test_continue_inside_loop_does_not_break_morphing_when_condition_changes(): void
    {
        Livewire::visit(new class extends Component {

            public bool $show = false;

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="showContent" dusk="show">Show</button>

                    @foreach ([1] as $item)
                        @if (true)
                            @continue(! $show)

                            <div dusk="content">Content</div>
                        @endif
                    @endforeach
                </div>
                HTML;
            }

            public function showContent(): void
            {
                $this->show = true;
            }
        })
        ->assertNotPresent('@content')
        ->waitForLivewire()->click('@show')
        ->assertVisible('@content')
        ->assertConsoleLogHasNoErrors()
        ;
    }
}
