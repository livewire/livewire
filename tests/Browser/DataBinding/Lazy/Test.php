<?php

namespace Tests\Browser\DataBinding\Lazy;

use Livewire\Livewire;
use Tests\Browser\TestCase;

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

    public function test_it_sends_input_lazy_request_before_checkbox_request_in_the_same_request()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, LazyInputsWithUpdatesDisplayedComponent::class)
                ->type('@name', 'bob')
                ->waitForLivewire()->check('@is_active')
                ->assertSeeIn('@totalNumberUpdates', 2)
                ->assertSeeIn('@updatesList', 'syncInput - name')
                ->assertSeeIn('@updatesList', 'syncInput - is_active')
            ;
        });
    }
}
