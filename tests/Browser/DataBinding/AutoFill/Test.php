<?php

namespace Tests\Browser\DataBinding\AutoFill;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertValue('@foo', '')
                ->assertValue('@bar', '')
                // Safari autofill changes input values without firing "input" events right away.
                ->runScript('document.querySelector(\'[dusk="foo"]\').value = "changed"')
                // Because we can't test with actual autofill, we are going to spoof it for Livewire.
                ->runScript('document.querySelector(\'[dusk="foo"]\').wasRecentlyAutofilled = true')
                ->pause(300)
                ->waitForLivewire()->type('@bar', 'changed')
                ->assertValue('@foo', 'changed')
                ->assertValue('@bar', 'changed')
            ;
        });
    }
}
