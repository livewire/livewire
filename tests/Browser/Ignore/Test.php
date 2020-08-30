<?php

namespace Tests\Browser\Ignore;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * wire:ignore doesnt modify element or children after update
                 */
                ->assertAttributeMissing('@foo', 'some-new-attribute')
                ->waitForLivewire()->click('@foo')
                ->assertAttributeMissing('@foo', 'some-new-attribute')

                /**
                 * wire:ignore ignores updates to children
                 */
                ->assertSeeIn('@bar.output', 'old')
                ->waitForLivewire()->click('@bar')
                ->assertSeeIn('@bar.output', 'old')

                /**
                 * wire:ignore.self ignores updates to self, but not children
                 */
                ->assertSeeIn('@baz.output', 'old')
                ->assertAttributeMissing('@baz', 'some-new-attribute')
                ->waitForLivewire()->click('@baz')
                ->assertAttributeMissing('@baz', 'some-new-attribute')
                ->assertSeeIn('@baz.output', 'new')

                /**
                 * adding .__livewire_ignore to element ignores updates to children
                 */
                ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"bob\"]').__livewire_ignore = true"); })
                ->assertSeeIn('@bob.output', 'old')
                ->waitForLivewire()->click('@bob')
                ->assertSeeIn('@bob.output', 'old')

                /**
                 * adding .__livewire_ignore_self to element ignores updates to self, but not children
                 */
                ->tap(function ($b) { $b->script("document.querySelector('[dusk=\"lob\"]').__livewire_ignore_self = true"); })
                ->assertSeeIn('@lob.output', 'old')
                ->assertAttributeMissing('@lob', 'some-new-attribute')
                ->waitForLivewire()->click('@lob')
                ->assertAttributeMissing('@lob', 'some-new-attribute')
                ->assertSeeIn('@lob.output', 'new')
                ;
        });
    }
}
