<?php

namespace Tests\Browser\Prefetch;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Prefetch\Component;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSeeIn('@count', '1')
                ->mouseover('@button')
                ->waitForLivewire()
                ->assertSeeIn('@count', '1')
                ->click('@button')
                ->assertSeeIn('@count', '2');
        });
    }
}
