<?php

namespace LegacyTests\Browser\Alpine\Emit;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test_dollar_wire_emit_works()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, EmitComponent::class)
                ->assertDontSee('emit worked')
                ->waitForLivewire()
                ->click('@emit')
                ->assertSee('emit worked')

                ->assertDontSee('emit self worked')
                ->waitForLivewire()
                ->click('@emitSelf')
                ->assertSee('emit self worked')

                ->assertDontSee('emit up worked')
                ->waitForLivewire()
                ->click('@emitUp')
                ->assertSee('emit up worked')

                ->assertDontSee('emit to worked')
                ->waitForLivewire()
                ->click('@emitTo')
                ->assertSee('emit to worked')
            ;
        });
    }
}
