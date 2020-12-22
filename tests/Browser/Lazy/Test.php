<?php

namespace Tests\Browser\Lazy;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Lazy\LazyInputsWithUpdatesDisplayedComponent;

class Test extends TestCase
{
    public function test_it_only_sends_updates_for_fields_that_have_been_changed_upon_submit()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, LazyInputsWithUpdatesDisplayedComponent::class)
                ->type('@name', 'bob')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@totalNumberUpdates', 2)
                ->assertSeeIn('@updatesList', 'syncInput - name')
                ->assertDontSeeIn('@updatesList', 'syncInput - description')
                ->assertSeeIn('@updatesList', 'callMethod - submit')

                ->type('@description', 'Test')
                ->waitForLivewire()->click('@submit')
                ->assertSeeIn('@totalNumberUpdates', 2)
                ->assertDontSeeIn('@updatesList', 'syncInput - name')
                ->assertSeeIn('@updatesList', 'syncInput - description')
                ->assertSeeIn('@updatesList', 'callMethod - submit')
            ;
        });
    }
}
