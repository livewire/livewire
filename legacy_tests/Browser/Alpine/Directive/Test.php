<?php

namespace LegacyTests\Browser\Alpine\Directive;

use LegacyTests\Browser\TestCase;

/** @group morphing */
class Test extends TestCase
{
    public function test_bind_x_data_after_livewire_commit()
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
