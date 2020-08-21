<?php

namespace Tests\Browser\Init;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * wire:init runs on page load.
                 */
                ->waitForLivewire()
                ->assertSeeIn('@output', 'foo')
            ;
        });
    }
}
