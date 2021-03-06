<?php

namespace Tests\Browser\Stack;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Stack\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                // Make sure events are registered in stacked html elements.
                ->assertDontSee('bar')
                ->click('@toggle')
                ->waitForLivewire()->pause(25)
                ->assertSee('bar')

                // After component update the stack is updated as well.
                ->assertSeeIn('@stack', 'baz')

                // Make sure nested components can push elements
                // and make sure prepends works like push.
                ->click('@toggleChild')
                ->waitForLivewire()->pause(25)
                ->assertSee('bob')
                ->assertSee('qux')

                // Check if removing stack element works.
                ->click('@toggleChild')
                ->waitForLivewire()->pause(25)
                ->assertDontSee('bob')
                ->assertDontSee('qux');
        });
    }
}
