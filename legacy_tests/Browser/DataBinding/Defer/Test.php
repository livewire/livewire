<?php

namespace LegacyTests\Browser\DataBinding\Defer;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Basic wire:model.defer
                 */
                ->type('@foo', 'foo')
                ->click('@foo.output')
                ->pause(150)
                ->assertDontSeeIn('@foo.output', 'foo')
                ->waitForLivewire()->click('@refresh')
                ->assertSeeIn('@foo.output', 'foo')

                /**
                 * wire:model.defer on two checkboxes
                 */
                ->assertNotChecked('@bar.a')
                ->assertNotChecked('@bar.b')
                ->check('@bar.a')
                ->check('@bar.b')
                ->click('@bar.output')
                ->pause(150)
                ->assertDontSeeIn('@bar.output', $expectation = '["a","b"]')
                ->waitForLivewire()->click('@refresh')
                ->assertSeeIn('@bar.output', $expectation)
            ;
        });
    }
}
