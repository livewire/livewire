<?php

namespace LegacyTests\Browser\Alpine\Directive;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test_dollar_wire_dispatch_works()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, DirectiveComponent::class)
                ->waitForLivewire()
                ->click('@button')
                ->assertConsoleLogMissingWarning('value is not defined')
            ;
        });
    }
}
