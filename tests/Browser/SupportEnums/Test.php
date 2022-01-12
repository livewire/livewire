<?php

namespace Tests\Browser\SupportEnums;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /**
     * @requires PHP >= 8.1
     */
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertSee('Be excellent to each other');
        });
    }
}
