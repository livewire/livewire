<?php

namespace LegacyTests\Browser\Init;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * wire:init runs on page load.
                 */
                ->waitForText('foo')
                ->assertSee('foo')
            ;
        });
    }
}
