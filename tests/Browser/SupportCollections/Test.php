<?php

namespace Tests\Browser\SupportCollections;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSeeIn('@things', 'foo')
                ->assertDontSeeIn('@things', 'bar')
                ->waitForLivewire()->click('@add-bar')
                ->assertSeeIn('@things', 'bar')
            ;
        });
    }
}
