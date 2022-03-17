<?php

namespace Tests\Browser\Alpine\Wire;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_nested_property_updates_via_wire()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, NestedPropertyUpdates::class);

            $browser
                ->assertScript('document.querySelector(\'[dusk="foo-server"]\').innerText', 'baz')
                ->assertScript('document.querySelector(\'[dusk="foo-input"]\').value', 'baz')
                ->waitForLivewire()
                ->type('@foo-input', 'car')
                ->assertScript('document.querySelector(\'[dusk="foo-server"]\').innerText', 'car')
                ->assertScript('document.querySelector(\'[dusk="foo-input"]\').value', 'car');

            $browser
                ->assertScript('document.querySelector(\'[dusk="fizz-server"]\').innerText', 'buzz')
                ->assertScript('document.querySelector(\'[dusk="fizz-input"]\').value', 'buzz')
                ->waitForLivewire()
                ->type('@fizz-input', 'fizzbuzz')
                ->assertScript('document.querySelector(\'[dusk="fizz-server"]\').innerText', 'fizzbuzz')
                ->assertScript('document.querySelector(\'[dusk="fizz-input"]\').value', 'fizzbuzz');
        });
    }
}
