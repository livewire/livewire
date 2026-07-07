<?php

namespace Livewire\Features\SupportRenderless;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_dont_call_render_using_renderless_attribute()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseRenderless]
            function renderlessAction() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button wire:click="renderlessAction" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }

    public function test_dont_call_render_using_renderless_modifier()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            function renderlessAction() { }

            function render()
            {
                $this->count++;

                return Blade::render(<<<'HTML'
                <div>
                    <button wire:click.renderless="renderlessAction" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }
}