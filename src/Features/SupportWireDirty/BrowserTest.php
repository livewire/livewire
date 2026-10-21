<?php

namespace Livewire\Features\SupportWireDirty;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_dirty_cleans_up_when_its_element_is_removed()
    {
        Livewire::visit(new class extends Component {
            public $foo = '';
            public $showIndicator = true;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input wire:model.lazy="foo" dusk="foo">

                        @if ($showIndicator)
                            <span wire:dirty.class="is-dirty" wire:target="foo" dusk="dirty">Dirty indicator</span>
                        @endif

                        <button type="button" wire:click="$toggle('showIndicator')" dusk="toggle"></button>
                        <button type="button" dusk="dummy"></button>
                    </div>
                HTML;
            }
        })
            ->tap(fn ($b) => $b->script("window.__removedEl = document.querySelector('[dusk=\"dirty\"]')"))
            ->waitForLivewire()->click('@toggle')
            ->waitUntilMissing('@dirty')
            ->type('@foo', 'bar')
            ->click('@dummy')
            // wire:dirty element already removed, leaked effect would be true
            ->assertScript('window.__removedEl.classList.contains("is-dirty")', false)
            ->clear('@foo')
            ->waitForLivewire()->click('@toggle')
            ->waitFor('@dirty')
            ->type('@foo', 'baz')
            ->click('@dummy')
            ->assertAttribute('@dirty', 'class', 'is-dirty')
            ->pause(150)
            ->assertAttributeDoesntContain('@dirty', 'class', 'is-dirty')
        ;
    }
}
