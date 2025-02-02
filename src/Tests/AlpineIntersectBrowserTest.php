<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class AlpineIntersectBrowserTest extends \Tests\BrowserTestCase
{
    public function test_component_with_intersect_plugin_works()
    {
        Livewire::visit(new class () extends Component {
            public function render()
            {
                return <<<'HTML'
                <div x-data="{ inViewport: false }">
                    <div x-intersect:enter="inViewport = true" x-intersect:leave="inViewport = false">something</div>
                    <p x-text="inViewport ? 'in viewport': 'outside viewport'"></p>
                    <button type="button" dusk="button" wire:click="$refresh">refresh</button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('p', 'in viewport')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('p', 'in viewport');
    }
}
