<?php

namespace LegacyTests\Browser\SupportEnums;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    /**
     * @requires PHP >= 8.1
     */
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertSee('Be excellent to each other');
        });
    }
}
