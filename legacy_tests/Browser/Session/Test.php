<?php

namespace LegacyTests\Browser\Session;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(); // @todo: Not sure how/if to handle this in V3 yet...
    }

    /** @test */
    public function it_shows_page_expired_dialog_when_session_has_expired()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->waitForLivewire()->click('@regenerateSession')
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
    public function it_shows_custom_hook_dialog_when_session_has_expired()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class, '?useCustomPageExpiredHook=true')
                ->waitForLivewire()->click('@regenerateSession')
                ->click('@refresh')
                // Wait for Livewire to respond, but dusk helper won't
                // work as dialog box is stopping further execution
                ->waitForDialog()
                ->assertDialogOpened('Page Expired')
                // Dismiss dialog so next tests run
                ->dismissDialog()
            ;
        });
    }
}
