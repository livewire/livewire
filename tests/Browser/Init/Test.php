<?php

namespace Tests\Browser\Init;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * wire:init runs on page load.
                 */
                ->waitForText('foo')
                ->assertSee('foo')
            ;
        });
    }
}
