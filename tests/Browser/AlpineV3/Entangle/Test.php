<?php

namespace Tests\Browser\AlpineV3\Entangle;

use Livewire\Livewire;
use Tests\Browser\Alpine\Entangle\Test as V2Test;

class Test extends V2Test
{
    public function setUp(): void
    {
        static::$useAlpineV3 = true;

        parent::setUp();
    }

    public function test_entangle_adds_fallback_when_property_in_array_is_not_set()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, EntangleWithFallback::class)
                ->assertSeeIn('@output.alpine.keys.0', '0')
                ->assertSeeIn('@output.alpine.keys.1', '0')
                ->assertSeeIn('@output.alpine.keys.2', '0')
                ->assertSeeIn('@output.alpine.keys.3', '0')
                ->assertSeeIn('@output.alpine.keys.4', '0')

                ->waitForLivewire()->click('@addKey1')
                ->assertSeeIn('@output.alpine.keys.0', '0')
                ->assertSeeIn('@output.alpine.keys.1', '1')
                ->assertSeeIn('@output.alpine.keys.2', '0')
                ->assertSeeIn('@output.alpine.keys.3', '0')
                ->assertSeeIn('@output.alpine.keys.4', '0')
                ->assertSeeIn('@output.livewire', '{"keys":[0,1,0,0,0]}')

                ->waitForLivewire()->click('@addKey3')
                ->assertSeeIn('@output.alpine.keys.0', '0')
                ->assertSeeIn('@output.alpine.keys.1', '1')
                ->assertSeeIn('@output.alpine.keys.2', '0')
                ->assertSeeIn('@output.alpine.keys.3', '3')
                ->assertSeeIn('@output.alpine.keys.4', '0')

                ->assertSeeIn('@output.livewire', '{"keys":[0,1,0,3,0]}')

                ->assertSeeIn('@output.livewire.users', '[{"name":"Caleb"}]')
                ->waitForLivewire()->click('@addUser')
                ->assertSeeIn('@output.livewire.users', '[{"name":"Caleb"},{"name":"Caleb Porzio"}]');
        });
    }
}
