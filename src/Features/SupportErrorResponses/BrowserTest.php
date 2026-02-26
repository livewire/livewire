<?php

namespace Livewire\Features\SupportErrorResponses;

use Livewire\Component as BaseComponent;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_it_shows_page_expired_dialog_when_session_has_expired()
    {
        if (app()->version() >= '13') {
            $this->markTestSkipped(
                'Laravel 13+ uses Sec-Fetch-Site origin verification which bypasses CSRF token checks for same-origin requests.'
            );
        }

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

    public function test_it_shows_custom_hook_dialog_using_on_error_response_hook_when_session_has_expired()
    {
        if (app()->version() >= '13') {
            $this->markTestSkipped(
                'Laravel 13+ uses Sec-Fetch-Site origin verification which bypasses CSRF token checks for same-origin requests.'
            );
        }

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

    public function test_it_does_not_show_html_modal_after_session_expired_dialog()
    {
        Livewire::visit(Component::class)
            ->waitForLivewire()->click('@regenerateSession')
            ->click('@refresh')
            ->waitForDialog()
            ->dismissDialog()
            ->pause(100) // Brief pause to ensure any modal would have rendered
            ->assertMissing('#livewire-error')
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

    @if($useCustomErrorResponseHook)
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail, preventDefault }) => {
                fail(({ status }) => {
                    if (status === 419) {
                        confirm('Page Expired - Error Response')

                        preventDefault()
                    }
                })
            })
        })
    </script>
    @endif
</div>
HTML;
    }
}
