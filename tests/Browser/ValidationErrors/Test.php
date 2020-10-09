<?php

namespace Tests\Browser\ValidationErrors;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Illuminate\Support\Facades\Artisan;

class Test extends TestCase
{
    public function test_validation_errors_are_returned_in_dollar_wire()
    {
        $this->browse(function ($browser) {
           Livewire::visit($browser, Component::class)
               ->assertDontSee('@errors')
               ->assertValue('@foo', '')
               ->click('@submit')
               ->assertSee('@errors');
        });
    }
}
