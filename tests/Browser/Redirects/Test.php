<?php

namespace Tests\Browser\Redirects;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Redirects\Component;

class Test extends TestCase
{
    /** @test */
    public function redirects()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Flashing a message shows up right away, AND
                 * will show up if you redirect to a different
                 * page right after.
                 */
                ->assertNotPresent('@flash.message')
                ->click('@flash')->waitForLivewire()
                ->assertPresent('@flash.message')
                ->click('@refresh')->waitForLivewire()
                ->assertNotPresent('@flash.message')
                ->click('@redirect-with-flash')->waitForReload()
                ->assertPresent('@flash.message')
                ->click('@refresh')->waitForLivewire()
                ->assertNotPresent('@flash.message');
        });
    }
}
