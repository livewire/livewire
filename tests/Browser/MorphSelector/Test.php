<?php

namespace Tests\Browser\MorphSelector;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * element root is DOM diffed
                 */
                ->assertSee('TBD')
                ->waitForLivewire()->click('@btn1')
                ->assertSeeIn('@result1', 'Clicked')
                ->waitForLivewire()->click('@btn2')
                ->assertSeeIn('@result2', 'Clicked')
                ->waitForLivewire()->click('@btn3')
                ->assertSeeIn('@result1', 'Foo')
                ->assertSeeIn('@result2', 'Bar')
            ;
        });
    }
}
