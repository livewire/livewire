<?php

namespace Tests\Browser\Events;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Events\Component;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tap(function ($browser) { $browser->script('window.livewire.emit("foo", "bar")'); })
                ->pause(350)
                ->assertSeeIn('@lastEventForParent', 'bar')
                ->assertSeeIn('@lastEventForChildA', 'bar')
                ->assertSeeIn('@lastEventForChildB', 'bar');
        });
    }
}
