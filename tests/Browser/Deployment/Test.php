<?php

namespace Tests\Browser\Deployment;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_shows_page_expired_dialog_when_livewire_deployment_invalidation_hash_has_changed()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
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

    /** @test */
    public function it_shows_page_expired_dialog_when_livewire_page_has_expired_exception_is_manually_thrown()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ManualDeploymentComponent::class)
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
