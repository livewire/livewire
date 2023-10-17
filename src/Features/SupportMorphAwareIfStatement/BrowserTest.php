<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function blade_conditionals_are_handled_properly_by_morphdom()
    {
        Livewire::visit(new class extends Component
        {
            public $show = true;

            public function toggle()
            {
                $this->show = ! $this->show;
            }

            public function render()
            {
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
            ->assertVisible('@foo');
    }

    /** @test */
    public function blade_conditional_actions_are_handled_properly_by_morphdom()
    {
        Livewire::visit(new class extends Component
        {
            public $enabled = true;

            public function enable()
            {
                $this->enabled = true;
            }

            public function disable()
            {
                $this->enabled = false;
            }

            public function render()
            {
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
            ->assertVisible('@disable');
    }
}
