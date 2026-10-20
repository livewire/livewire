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
                            <span wire:dirty.class="is-dirty" wire:target="foo" dusk="dirty">Dirty Indicator</span>
                        @endif

                        <button type="button" wire:click="$toggle('showIndicator')" dusk="toggle"></button>
                        <button type="button" dusk="dummy"></button>
                    </div>
                HTML;
            }
        })
            ->assertPresent('@dirty')
            ->assertAttributeDoesntContain('@dirty', 'class', 'is-dirty')
            ->tap(fn ($b) => $b->script("
                window.__removedEl = document.querySelector('[dusk=\"dirty\"]')
            "))
            ->waitForLivewire()->click('@toggle')
            ->waitUntilMissing('@dirty')
            ->type('@foo', 'bar')
            ->pause(100)
            ->assertScript('window.__removedEl.classList.contains("is-dirty")', false)
            ->click('@dummy')
            ->assertNotPresent('@dirty')
            ->clear('@foo')
            ->waitForLivewire()->click('@toggle')
            ->waitFor('@dirty')
            ->assertAttributeDoesntContain('@dirty', 'class', 'is-dirty')
            ->type('@foo', 'baz')
            ->pause(100)
            ->assertAttribute('@dirty', 'class', 'is-dirty')
            ->click('@dummy')
            ->pause(100)
            ->assertAttributeDoesntContain('@dirty', 'class', 'is-dirty')
        ;
    }
}
