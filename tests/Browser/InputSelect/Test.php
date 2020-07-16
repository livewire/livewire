<?php

namespace Tests\Browser\InputSelect;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->tinker()
                ->assertSelected('@single.input', null)
                ->assertSeeIn('@single.output', '')
                ->select('@single.input', 'foo')
                ->waitForLivewire()
                ->assertSelected('@single.input', 'foo')
                ->assertSeeIn('@single.output', 'foo');
        });
    }
}
