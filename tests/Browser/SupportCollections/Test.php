<?php

namespace Tests\Browser\SupportCollections;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\SupportCollections\Component;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSee('foo')
                ->assertDontSee('bar')
                ->click('@add-bar')
                ->waitForLivewire()
                ->assertSee('bar');
        });
    }
}
