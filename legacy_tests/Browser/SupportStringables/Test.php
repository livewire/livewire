<?php

namespace LegacyTests\Browser\SupportStringables;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertSee('Be excellent to each other');
        });
    }
}
