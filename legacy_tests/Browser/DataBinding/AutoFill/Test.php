<?php

namespace LegacyTests\Browser\DataBinding\AutoFill;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        // This is a manual test for Safari.
        // We can't test this automically because
        // Safari's automation mode disables autofill.
        //
        // Test steps:
        // - Comment out the "markTextSkipped" below
        // - Run test in chrome
        // - Copy the URL and paste it into Safari
        // - Autofill the email/password fields
        // - Assert both fields are filled
        // - Assert both values are synced with Livewire
        $this->markTestSkipped();

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)->tinker();
        });
    }
}
