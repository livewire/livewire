<?php

namespace Tests\Browser\Morph;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_does_not_break_nested_livewire_components_when_a_previous_sibling_element_added()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, WithNestedLivewireComponent::class)
                ->assertDontSeeIn('@output', 'first')
                ->assertSeeIn('@nestedOutput', 'false')
                ->waitForLivewire()->click('@toggleNested')
                ->assertSeeIn('@nestedOutput', 'true')
                ->waitForLivewire()->click('@togglePreviousChild')
                ->assertSeeIn('@output', 'first')
                ->assertSeeIn('@nestedOutput', 'true')
            ;
        });
    }

    /** @test */
    public function it_does_not_break_alpine_components_when_a_previous_sibling_element_added()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, WithAlpineComponent::class)
                ->assertDontSeeIn('@output', 'first')
                ->assertSeeIn('@alpineOutput', 'false')
                ->click('@toggleAlpine')
                ->assertSeeIn('@alpineOutput', 'true')
                ->waitForLivewire()->click('@togglePreviousChild')
                ->assertSeeIn('@output', 'first')
                ->assertSeeIn('@alpineOutput', 'true')
            ;
        });
    }
}
