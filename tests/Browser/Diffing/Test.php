<?php


namespace Tests\Browser\Diffing;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSee('__TEXT_NODE__')
                ->waitForLivewire()->click('@toggle')
                ->assertSee('__TEXT_NODE__')
                ->waitForLivewire()->click('@toggle');
        });
    }
}