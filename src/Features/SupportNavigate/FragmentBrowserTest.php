<?php

namespace Livewire\Features\SupportNavigate;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class FragmentBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Livewire::component('fragment-page', FragmentPage::class);
            Route::get('/fragment', FragmentPage::class)->middleware('web');
            Route::get('/fragment-other', FragmentPage::class)->middleware('web');
        };
    }

    public function test_same_page_fragment_links_do_not_fetch_or_swap_the_page()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment')->click('@increment')->type('@draft', 'Unsaved draft');
            $browser->script('window.fragmentRequests = 0; Livewire.hook("navigate.request", () => window.fragmentRequests++)');

            $browser
                ->waitForNoNavigatePrefetchRequest()->mouseover('@comments')
                ->waitForNavigate()->click('@comments')
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->assertSeeIn('@count', '1')
                ->assertInputValue('@draft', 'Unsaved draft')
                ->assertScript('window.fragmentRequests', 0)
                ->assertConsoleLogHasNoErrors();
        });
    }

    public function test_fragment_history_keeps_the_live_page_and_restores_scroll()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment')->click('@increment');
            $browser->script('window.fragmentRequests = 0; Livewire.hook("navigate.request", () => window.fragmentRequests++)');

            $browser
                ->waitForNavigate()->click('@comments')
                ->assertInViewPort('@comments-target')
                ->waitForNavigate()->click('@replies')
                ->assertInViewPort('@replies-target')
                ->waitForNavigate()->back()
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->assertSeeIn('@count', '1')
                ->waitForNavigate()->back()
                ->assertFragmentIs('')
                ->assertScript('window.scrollY', 0)
                ->assertSeeIn('@count', '1')
                ->waitForNavigate()->forward()
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->assertSeeIn('@count', '1')
                ->assertScript('window.fragmentRequests', 0);
        });
    }

    public function test_fragment_history_works_after_visiting_another_page()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/fragment')
                ->waitForNavigate()->click('@comments')
                ->waitForNavigate()->click('@other')
                ->assertPathIs('/fragment-other')
                ->waitForNavigate()->back()
                ->assertPathIs('/fragment')
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->click('@increment')
                ->waitForNavigate()->back()
                ->assertFragmentIs('')
                ->assertSeeIn('@count', '1')
                ->waitForNavigate()->forward()
                ->assertFragmentIs('comments')
                ->assertSeeIn('@count', '1')
                ->waitForNavigate()->forward()
                ->assertPathIs('/fragment-other');
        });
    }

    public function test_programmatic_fragment_navigation_and_preserve_scroll()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment')->click('@increment');
            $browser->script('window.fragmentRequests = 0; Livewire.hook("navigate.request", () => window.fragmentRequests++)');

            $browser
                ->waitForNavigate()->click('@preserve')
                ->assertFragmentIs('comments')
                ->assertScript('window.scrollY', 0)
                ->waitForNavigate()->click('@programmatic-preserve')
                ->assertFragmentIs('replies')
                ->assertScript('window.scrollY', 0)
                ->waitForNavigate()->click('@programmatic')
                ->assertFragmentIs('replies')
                ->assertInViewPort('@replies-target')
                ->assertSeeIn('@count', '1')
                ->assertScript('window.fragmentRequests', 0);
        });
    }

    public function test_fragment_links_support_keyboard_activation_and_repeated_clicks()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment')->click('@increment');
            $browser->script('window.fragmentRequests = 0; window.fragmentHistoryLength = history.length; Livewire.hook("navigate.request", () => window.fragmentRequests++)');

            $browser
                ->waitForNavigate()->keys('@comments', '{enter}')
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target');

            $browser->script('window.scrollTo(0, 0)');

            $browser
                ->waitForNavigate()->click('@comments')
                ->assertInViewPort('@comments-target')
                ->assertScript('history.length === window.fragmentHistoryLength + 1', true)
                ->assertSeeIn('@count', '1')
                ->assertScript('window.fragmentRequests', 0);
        });
    }

    public function test_empty_fragments_scroll_to_top_without_swapping()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/fragment')
                ->click('@increment')
                ->waitForNavigate()->click('@comments')
                ->assertInViewPort('@comments-target')
                ->waitForNavigate()->click('@top')
                ->assertScript('window.scrollY', 0)
                ->assertSeeIn('@count', '1');
        });
    }

    public function test_fragment_navigation_is_cancellable_and_does_not_fire_swap_events()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment');
            $browser->script(<<<'JS'
                window.fragmentRequests = 0;
                window.fragmentSwaps = 0;
                Livewire.hook('navigate.request', () => window.fragmentRequests++);
                document.addEventListener('livewire:navigating', () => window.fragmentSwaps++);
                document.addEventListener('livewire:navigate', event => event.preventDefault(), { once: true });
            JS);

            $browser
                ->click('@comments')
                ->assertFragmentIs('')
                ->assertScript('window.scrollY', 0)
                ->waitForNavigate()->click('@comments')
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->assertScript('window.fragmentRequests', 0)
                ->assertScript('window.fragmentSwaps', 0);
        });
    }

    public function test_fragment_history_restores_marked_scroll_containers()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment');
            $browser->script('document.getElementById("pane").scrollTop = 100');

            $browser->waitForNavigate()->click('@comments');
            $browser->script('document.getElementById("pane").scrollTop = 200');

            $browser
                ->waitForNavigate()->back()
                ->assertScript('document.getElementById("pane").scrollTop', 100)
                ->waitForNavigate()->forward()
                ->assertScript('document.getElementById("pane").scrollTop', 200);
        });
    }

    public function test_fragment_history_works_after_a_browser_refresh()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/fragment')
                ->waitForNavigate()->click('@comments')
                ->waitForNavigate()->click('@replies')
                ->refresh()
                ->waitForNavigate()->back()
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->click('@increment')
                ->waitForNavigate()->back()
                ->assertFragmentIs('')
                ->assertSeeIn('@count', '1');
        });
    }

    public function test_visiting_the_same_url_without_a_fragment_still_swaps_the_page()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/fragment')
                ->click('@increment')
                ->waitForNavigate()->click('@reload')
                ->assertSeeIn('@count', '0')
                ->click('@increment')
                ->click('@increment')
                ->waitForNavigate()->back()
                ->assertSeeIn('@count', '0');
        });
    }

    public function test_a_query_string_change_still_fetches_and_swaps_the_page()
    {
        $this->browse(function ($browser) {
            $browser->visit('/fragment')->click('@increment');
            $browser->script('window.fragmentRequests = 0; Livewire.hook("navigate.request", () => window.fragmentRequests++)');

            $browser
                ->waitForNavigate()->click('@query')
                ->assertQueryStringHas('page', '2')
                ->assertFragmentIs('comments')
                ->assertInViewPort('@comments-target')
                ->assertSeeIn('@count', '0')
                ->assertScript('window.fragmentRequests', 1);
        });
    }
}

class FragmentPage extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div x-data="{ count: 0 }">
            <nav style="position: fixed; top: 0; right: 0;">
                <button x-on:click="count++" dusk="increment">Increment</button>
                <span x-text="count" dusk="count"></span>
                <input dusk="draft">
                <a href="#comments" wire:navigate.hover dusk="comments">Comments</a>
                <a href="#" wire:navigate dusk="top">Top</a>
                <a href="/fragment#replies" wire:navigate dusk="replies">Replies</a>
                <a href="#comments" wire:navigate.preserve-scroll dusk="preserve">Preserve scroll</a>
                <button x-on:click="Livewire.navigate('/fragment#replies', { preserveScroll: true })" dusk="programmatic-preserve">Programmatic preserve scroll</button>
                <button x-on:click="Livewire.navigate('/fragment#replies')" dusk="programmatic">Programmatic</button>
                <a href="/fragment?page=2#comments" wire:navigate dusk="query">Page two</a>
                <a href="/fragment" wire:navigate dusk="reload">Reload</a>
                <a href="/fragment-other" wire:navigate dusk="other">Other page</a>
            </nav>
            <div id="pane" wire:navigate:scroll style="position: fixed; left: 0; bottom: 0; height: 50px; width: 100px; overflow: auto;">
                <div style="height: 500px;">Scrollable pane</div>
            </div>
            <div style="height: 200vh;"></div>
            <div id="comments" dusk="comments-target">Comments</div>
            <div style="height: 200vh;"></div>
            <div id="replies" dusk="replies-target">Replies</div>
            <div style="height: 200vh;"></div>
        </div>
        HTML;
    }
}
