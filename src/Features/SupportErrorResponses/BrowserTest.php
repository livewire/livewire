<?php

namespace Livewire\Features\SupportErrorResponses;

use Illuminate\Support\Facades\Route;
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

    public function test_only_the_model_that_started_a_failed_request_is_reverted()
    {
        Livewire::visit(new class extends BaseComponent {
            public $count = 0;
            public $form = [
                'name' => '',
                'notes' => '',
            ];

            public function updatedForm($value, $key)
            {
                if ($key === 'name' || ($key === null && $value['name'] !== '')) {
                    abort(500);
                }
            }

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="name" wire:model.live="form.name" />
                    <input dusk="notes" wire:model="form.notes" />

                    <button dusk="increment" wire:click="increment">Increment</button>

                    <span dusk="count">{{ $count }}</span>
                </div>
                HTML;
            }
        })
            // This deferred draft will be bundled into the failed model request...
            ->type('@notes', 'Keep this draft')
            ->type('@name', 'x')
            ->waitFor('#livewire-error')
            ->keys('#livewire-error', '{escape}')
            ->waitUntilMissing('#livewire-error')
            // Revert only the model that initiated the request...
            ->assertValue('@name', '')
            ->assertValue('@notes', 'Keep this draft')
            // The rejected model is no longer replayed and unrelated state can sync...
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->assertValue('@notes', 'Keep this draft')
            ->pause(100)
            ->assertMissing('#livewire-error')
        ;
    }

    public function test_deferred_form_values_survive_a_failed_action()
    {
        Livewire::visit(new class extends BaseComponent {
            public $count = 0;
            public $first = '';
            public $last = '';

            public function save()
            {
                abort(500);
            }

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="first" wire:model="first" />
                    <input dusk="last" wire:model="last" />

                    <button dusk="save" wire:click="save">Save</button>
                    <button dusk="increment" wire:click="increment">Increment</button>

                    <span dusk="count">{{ $count }}</span>
                </div>
                HTML;
            }
        })
            ->type('@first', 'Ada')
            ->type('@last', 'Lovelace')
            ->click('@save')
            ->waitFor('#livewire-error')
            ->keys('#livewire-error', '{escape}')
            ->waitUntilMissing('#livewire-error')
            // An action error says nothing about whether these updates were accepted...
            ->assertValue('@first', 'Ada')
            ->assertValue('@last', 'Lovelace')
            // A different action can accept the drafts and continue normally...
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->assertValue('@first', 'Ada')
            ->assertValue('@last', 'Lovelace')
            ->pause(100)
            ->assertMissing('#livewire-error')
        ;
    }

    public function test_it_does_not_show_html_modal_after_session_expired_dialog()
    {
        if (app()->version() >= '13') {
            $this->markTestSkipped(
                'Laravel 13+ uses Sec-Fetch-Site origin verification which bypasses CSRF token checks for same-origin requests.'
            );
        }

        Livewire::visit(Component::class)
            ->waitForLivewire()->click('@regenerateSession')
            ->click('@refresh')
            ->waitForDialog()
            ->dismissDialog()
            ->pause(100) // Brief pause to ensure any modal would have rendered
            ->assertMissing('#livewire-error')
        ;
    }

    public function test_it_shows_a_refresh_dialog_when_the_server_returns_an_empty_successful_response(): void
    {
        $this->returnEmptyUpdateResponses();

        Livewire::visit(Component::class)
            ->click('@refresh')
            ->waitForDialog()
            ->assertDialogOpened("The server returned an empty response.\nWould you like to refresh the page?")
            ->dismissDialog()
            ->pause(100)
            ->assertMissing('#livewire-error')
        ;
    }

    public function test_it_allows_the_empty_response_dialog_to_be_customised_using_a_request_interceptor(): void
    {
        $this->returnEmptyUpdateResponses();

        Livewire::withQueryParams(['useCustomEmptyResponseInterceptor' => true])
            ->visit(Component::class)
            ->click('@refresh')
            ->waitForDialog()
            ->assertDialogOpened('Empty Response - Custom Error Response')
            ->dismissDialog()
            ->pause(100)
            ->assertMissing('#livewire-error')
        ;
    }

    protected function returnEmptyUpdateResponses(): void
    {
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, fn () => response('', 200, ['Content-Type' => 'application/json']));
        });
    }
}

class Component extends BaseComponent
{
    public $useCustomPageExpiredHook = false;
    public $useCustomErrorResponseHook = false;
    public bool $useCustomEmptyResponseInterceptor = false;

    protected $queryString = [
        'useCustomPageExpiredHook' => ['except' => false],
        'useCustomErrorResponseHook' => ['except' => false],
        'useCustomEmptyResponseInterceptor' => ['except' => false],
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

    @if($useCustomEmptyResponseInterceptor)
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.interceptRequest(({ onError }) => {
                onError(({ response, body, preventDefault }) => {
                    if (response.ok && body === '') {
                        confirm('Empty Response - Custom Error Response')

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
