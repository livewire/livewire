<?php

namespace LegacyTests\Browser\Deployment;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test_it_shows_page_expired_dialog_when_livewire_deployment_invalidation_hash_has_changed()
    {
        $this->markTestSkipped(); // @todo: Josh Hanley

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->click('@refresh')
                // Wait for Livewire to respond, but dusk helper won't
                // work as dialog box is stopping further execution
                ->waitForDialog()
                ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
                // Dismiss dialog so next tests run
                ->dismissDialog()
            ;
        });
    }

    public function test_it_shows_page_expired_dialog_when_livewire_page_has_expired_exception_is_manually_thrown()
    {
        $this->markTestSkipped(); // @todo: Josh Hanley

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ManualDeploymentComponent::class)
                ->click('@invalidateComponent')
                // Wait for Livewire to respond, but dusk helper won't
                // work as dialog box is stopping further execution
                ->waitForDialog()
                ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
                // Dismiss dialog so next tests run
                ->dismissDialog()
            ;
        });
    }
}
