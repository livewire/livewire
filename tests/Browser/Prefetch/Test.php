<?php

namespace Tests\Browser\Prefetch;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSeeIn('@count', '1')
                ->mouseover('@button')
                ->pause(250) // We have to pause because prefetching doesn't call normal response hooks.
                ->assertSeeIn('@count', '1')
                ->click('@button')
                ->assertSeeIn('@count', '2');
        });
    }
}
