<?php

namespace Livewire\Features\SupportNavigate;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;

class HooksBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            View::addNamespace('test-views', __DIR__ . '/test-views');

            Livewire::component('page', NthPage::class);

            Route::get('/page', NthPage::class)->middleware('web');
        };
    }

    public function test_navigation_triggers_lifecycle_hooks()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/page')
                ->assertScript('return window.__hooks[0].name === "livewire:navigated"')
                ->assertScript('return window.__hooks[1] === undefined')
                ->waitForNavigate()->click('@link.to.2')
                ->assertScript('return window.__hooks[1].name === "livewire:navigate" && window.__hooks[1].detail.history === false && window.__hooks[1].detail.cached === false')
                ->assertScript('return window.__hooks[2].name === "livewire:navigating"')
                ->assertScript('return window.__hooks[3].name === "livewire:navigated"')
            ;
        });
    }

    public function test_back_and_forward_button_triggers_the_same_lifecycle_hooks_as_a_normal_navigate()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/page')
                ->assertScript('return window.__hooks[0].name === "livewire:navigated"')
                ->assertScript('return window.__hooks[1] === undefined')
                ->waitForNavigate()->click('@link.to.2')
                ->assertScript('return window.__hooks[1].name === "livewire:navigate" && window.__hooks[1].detail.history === false && window.__hooks[1].detail.cached === false')
                ->assertScript('return window.__hooks[2].name === "livewire:navigating"')
                ->assertScript('return window.__hooks[3].name === "livewire:navigated"')
                ->waitForNavigate()->back()
                ->assertScript('return window.__hooks[4].name === "livewire:navigate" && window.__hooks[4].detail.history === true && window.__hooks[4].detail.cached === true')
                ->assertScript('return window.__hooks[5].name === "livewire:navigating"')
                ->assertScript('return window.__hooks[6].name === "livewire:navigated"')
                ->waitForNavigate()->forward()
                ->assertScript('return window.__hooks[7].name === "livewire:navigate" && window.__hooks[7].detail.history === true && window.__hooks[7].detail.cached === true')
                ->assertScript('return window.__hooks[8].name === "livewire:navigating"')
                ->assertScript('return window.__hooks[9].name === "livewire:navigated"')
            ;
        });
    }

    public function test_back_button_hook_contains_info_about_caching_after_the_cache_runs_out()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/page')
                ->waitForNavigate()->click('@link.to.2')
                ->waitForNavigate()->click('@link.to.3')
                ->waitForNavigate()->click('@link.to.4')
                ->waitForNavigate()->click('@link.to.5')
                ->waitForNavigate()->click('@link.to.6')
                ->waitForNavigate()->click('@link.to.7')
                ->waitForNavigate()->click('@link.to.8')
                ->waitForNavigate()->click('@link.to.9')
                ->waitForNavigate()->click('@link.to.10')
                ->waitForNavigate()->click('@link.to.11')
                ->waitForNavigate()->click('@link.to.12')
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->waitForNavigate()->back()
                ->assertScript('return window.__hooks[64].name === "livewire:navigate" && window.__hooks[64].detail.history === true && window.__hooks[64].detail.cached === false')
            ;
        });
    }
}

class NthPage extends Component
{
    #[Url]
    public $number = 1;

    public function redirectToPageTwoUsingNavigate()
    {
        return $this->redirect('/page?number=2', navigate: true);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On page: {{ $number }}</div>

            <a href="/page?number=1" wire:navigate.hover dusk="link.to.1">Go to first page</a>
            <a href="/page?number=2" wire:navigate.hover dusk="link.to.2">Go to second page</a>
            <a href="/page?number=3" wire:navigate.hover dusk="link.to.3">Go to third page</a>
            <a href="/page?number=4" wire:navigate.hover dusk="link.to.4">Go to fourth page</a>
            <a href="/page?number=5" wire:navigate.hover dusk="link.to.5">Go to fifth page</a>
            <a href="/page?number=6" wire:navigate.hover dusk="link.to.6">Go to sixth page</a>
            <a href="/page?number=7" wire:navigate.hover dusk="link.to.7">Go to seventh page</a>
            <a href="/page?number=8" wire:navigate.hover dusk="link.to.8">Go to eighth page</a>
            <a href="/page?number=9" wire:navigate.hover dusk="link.to.9">Go to nineth page</a>
            <a href="/page?number=10" wire:navigate.hover dusk="link.to.10">Go to tenth page</a>
            <a href="/page?number=11" wire:navigate.hover dusk="link.to.11">Go to eleventh page</a>
            <a href="/page?number=11" wire:navigate.hover dusk="link.to.12">Go to twelfth page</a>

            <button type="button" wire:click="redirectToPageTwoUsingNavigate" dusk="redirect.to.second">Redirect to second page</button>

            @assets
            <script>
                window.__hooks = []

                document.addEventListener('livewire:navigate', (e) => {
                    window.__hooks.push({ name: 'livewire:navigate', detail: e.detail })
                    console.log('livewire:navigate', e.detail)
                })

                document.addEventListener('livewire:navigating', (e) => {
                    window.__hooks.push({ name: 'livewire:navigating', detail: e.detail })
                    console.log('livewire:navigating', e.detail)
                })

                document.addEventListener('livewire:navigated', (e) => {
                    window.__hooks.push({ name: 'livewire:navigated', detail: e.detail })
                    console.log('livewire:navigated', e.detail)
                })
            </script>
            @endassets
        </div>
        HTML;
    }
}
