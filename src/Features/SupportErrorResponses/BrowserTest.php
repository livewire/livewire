<?php

namespace Livewire\Features\SupportErrorResponses;

use Livewire\Component as BaseComponent;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function it_shows_page_expired_dialog_when_session_has_expired()
    {
        Livewire::visit(Component::class)
            ->waitForLivewire()->click('@regenerateSession')
            ->click('@refresh')
            // Wait for Livewire to respond, but dusk helper won't
            // work as dialog box is stopping further execution
            ->waitForDialog()
            ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
            // Dismiss dialog so next tests run
            ->dismissDialog()
        ;
    }

    /** @test */
    public function it_shows_custom_hook_dialog_when_session_has_expired()
    {
        $this->markTestSkipped(); // @todo: Delete this test if we decide to remove the old hook or fix implementation

        Livewire::visit(Component::class)
            ->waitForLivewire()->click('@regenerateSession')
            ->click('@refresh')
            // Wait for Livewire to respond, but dusk helper won't
            // work as dialog box is stopping further execution
            ->waitForDialog()
            ->assertDialogOpened('Page Expired')
            // Dismiss dialog so next tests run
            ->dismissDialog()
        ;
    }

    /** @test */
    public function it_shows_custom_hook_dialog_using_on_error_response_hook_when_session_has_expired()
    {
        Livewire::withQueryParams(['useCustomErrorResponseHook' => true])
            ->visit(Component::class)
            ->waitForLivewire()->click('@regenerateSession')
            ->click('@refresh')
            // Wait for Livewire to respond, but dusk helper won't
            // work as dialog box is stopping further execution
            ->waitForDialog()
            ->assertDialogOpened('Page Expired - Error Response')
            // Dismiss dialog so next tests run
            ->dismissDialog()
        ;
    }
}

class Component extends BaseComponent
{
    public $useCustomPageExpiredHook = false;
    public $useCustomErrorResponseHook = false;

    protected $queryString = [
        'useCustomPageExpiredHook' => ['except' => false],
        'useCustomErrorResponseHook' => ['except' => false],
    ];

    public function regenerateSession()
    {
        request()->session()->regenerate();
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="regenerateSession" dusk="regenerateSession">Regenerate Session</button>
    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>

    @if($useCustomPageExpiredHook)
    {{-- // @todo: Remove this if we decide to remove the old hook --}}
    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.onPageExpired(() => confirm('Page Expired'))
        })
    </script>
    @endif

    @if($useCustomErrorResponseHook)
    <script>
        // @todo: Change this to a Livewire init hook once implemented
        document.addEventListener('alpine:init', () => {
            Livewire.hook('response.error', (response, content, skipDefault) => {
                if (response.status === 419) {
                    confirm('Page Expired - Error Response')

                    skipDefault()
                }
            })
        })
    </script>
    @endif
</div>
HTML;
    }
}
