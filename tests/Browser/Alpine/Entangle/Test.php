<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\Entangle\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Can mutate an array in Alpine and reflect in Livewire.
                 */
                ->assertDontSeeIn('@output.alpine', 'baz')
                ->assertDontSeeIn('@output.blade', 'baz')
                ->waitForLivewire()->click('@button')
                ->assertSeeIn('@output.alpine', 'baz')
                ->assertSeeIn('@output.blade', 'baz')

                /**
                 * Can conditionally load in a new Alpine component that uses @entangle
                 */
                ->assertNotPresent('@bob.alpine')
                ->assertSeeIn('@bob.blade', 'before')
                ->waitForLivewire()->click('@bob.show')
                ->assertSeeIn('@bob.alpine', 'before')
                ->assertSeeIn('@bob.blade', 'before')
                ->waitForLivewire()->click('@bob.button')
                ->assertSeeIn('@bob.alpine', 'after')
                ->assertSeeIn('@bob.blade', 'after')
            ;
        });
    }

    public function test_watcher_is_fired_when_entangled_update_changes_other_entangled_data()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ChangeMultipleDataAtTheSameTime::class)
                ->assertSeeIn('@output.alpine', 1)
                ->assertSeeIn('@output.alpine', 2)
                ->assertSeeIn('@output.alpine', 3)
                ->assertSeeIn('@output.alpine', 4)
                ->assertSeeIn('@output.livewire', 1)
                ->assertSeeIn('@output.livewire', 2)
                ->assertSeeIn('@output.livewire', 3)
                ->assertSeeIn('@output.livewire', 4)
                ->waitForLivewire()->type('@search', 's')
                ->assertSeeIn('@output.alpine', 5)
                ->assertSeeIn('@output.alpine', 6)
                ->assertSeeIn('@output.alpine', 7)
                ->assertSeeIn('@output.alpine', 8)
                ->assertSeeIn('@output.livewire', 5)
                ->assertSeeIn('@output.livewire', 6)
                ->assertSeeIn('@output.livewire', 7)
                ->assertSeeIn('@output.livewire', 8)
            ;
        });
    }

    public function test_watcher_is_fired_each_time_entangled_data_changes()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ToggleEntangled::class)
                ->assertSeeIn('@output.alpine', 'false')
                ->assertSeeIn('@output.livewire', 'false')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output.alpine', 'true')
                ->assertSeeIn('@output.livewire', 'true')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output.alpine', 'false')
                ->assertSeeIn('@output.livewire', 'false')
            ;
        });
    }

    public function test_dot_defer()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, DeferDataUpdates::class)
                ->type('@input', 's')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@output.alpine', 's')
                ->assertSeeIn('@output.livewire', 's')
                ->append('@input', 's')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@output.alpine', 'ss')
                ->assertSeeIn('@output.livewire', 'ss')
            ;
        });
    }
}
