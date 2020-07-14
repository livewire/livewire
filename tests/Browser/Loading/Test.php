<?php

namespace Tests\Browser\Loading;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Loading\Component;

class Test extends TestCase
{
    /** @test */
    public function loading_indicator()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertNotVisible('@indicator')
                ->click('@button')
                ->waitForLivewireRequest()
                ->assertVisible('@indicator')
                ->waitForLivewireResponse()
                ->assertNotVisible('@indicator');
        });
    }
}
