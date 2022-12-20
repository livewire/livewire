<?php

namespace Tests\Browser\AlpineV3;

use Livewire\Livewire;
use Tests\Browser\Alpine\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }

    public function test_alpine_registers_correct_number_of_listeners_for_x_model_checkbox_on_livewire_change()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, XModelComponent::class)
                ->waitForLivewire()
                ->click('@show')
                ->click('@plz-check-me-caleb')
                ->assertSeeIn('@alpineChecksLength', 1)
                ->assertSeeIn('@alpineChecksValue', '1')
            ;
        });
    }
}
