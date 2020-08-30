<?php

namespace Tests\Browser\SupportCollections;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\SupportCollections\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSee('foo')
                ->assertDontSee('bar')
                ->waitForLivewire()->click('@add-bar')
                ->assertSee('bar');
        });
    }
}
