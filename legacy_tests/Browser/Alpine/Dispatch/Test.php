<?php

namespace LegacyTests\Browser\Alpine\Dispatch;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test_dollar_wire_dispatch_works()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ['parent' => DispatchComponent::class, 'child' => DispatchNestedComponent::class])
                ->assertDontSee('dispatch worked')
                ->waitForLivewire()
                ->click('@dispatch')
                ->assertSee('Dispatch worked')

                ->assertDontSee('Dispatch self worked')
                ->waitForLivewire()
                ->click('@dispatchSelf')
                ->assertSee('Dispatch self worked')

                ->assertDontSee('Dispatch to worked')
                ->waitForLivewire()
                ->click('@dispatchTo')
                ->assertSee('Dispatch to worked')
            ;
        });
    }
}
