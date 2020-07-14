<?php

namespace Tests\Browser\Loading;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Illuminate\Support\Facades\View;
use Tests\Browser\Loading\Component;
use Illuminate\Support\Facades\Route;

class Test extends TestCase
{
    /** @test */
    public function something()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertNotVisible('@indicator')
                ->click('@button')
                ->waitForLivewireRequest()
                ->assertVisible('@indicator')
                ->waitForLivewireResponse()
                ->assertNotVisible('@indicator');
        });
    }
}
