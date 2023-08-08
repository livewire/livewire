<?php

namespace Livewire\Features\SupportNavigate;

use Livewire\Attributes\Url;
use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook() {
        return function() {
            View::addNamespace('test-views', __DIR__.'/test-views');

            Livewire::component('query-page', QueryPage::class);
            Livewire::component('first-page', FirstPage::class);
            Livewire::component('first-page-with-link-outside', FirstPageWithLinkOutside::class);
            Livewire::component('second-page', SecondPage::class);
            Livewire::component('third-page', ThirdPage::class);
            Livewire::component('first-asset-page', FirstAssetPage::class);
            Livewire::component('second-asset-page', SecondAssetPage::class);
            Livewire::component('third-asset-page', ThirdAssetPage::class);
            Livewire::component('first-tracked-asset-page', FirstTrackedAssetPage::class);
            Livewire::component('second-tracked-asset-page', SecondTrackedAssetPage::class);
            Livewire::component('first-scroll-page', FirstScrollPage::class);
            Livewire::component('second-scroll-page', SecondScrollPage::class);

            Route::get('/query-page', QueryPage::class)->middleware('web');
            Route::get('/first', FirstPage::class)->middleware('web');
            Route::get('/first-hide-progress', function () {
                config(['livewire.navigate.show_progress_bar' => false]);

                return (new FirstPage)();
            })->middleware('web');
            Route::get('/first-outside', FirstPageWithLinkOutside::class)->middleware('web');
            Route::get('/second', SecondPage::class)->middleware('web');
            Route::get('/third', ThirdPage::class)->middleware('web');
            Route::get('/first-asset', FirstAssetPage::class)->middleware('web');
            Route::get('/second-asset', SecondAssetPage::class)->middleware('web');
            Route::get('/third-asset', ThirdAssetPage::class)->middleware('web');
            Route::get('/first-scroll', FirstScrollPage::class)->middleware('web');
            Route::get('/second-scroll', SecondScrollPage::class)->middleware('web');

            Route::get('/first-tracked-asset', FirstTrackedAssetPage::class)->middleware('web');
            Route::get('/second-tracked-asset', SecondTrackedAssetPage::class)->middleware('web');

            Route::get('/test-navigate-asset.js', function () {
                return Utils::pretendResponseIsFile(__DIR__.'/test-views/test-navigate-asset.js');
            });
        };
    }

    /** @test */
    function can_configure_progress_bar()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.third')
                ->waitFor('#nprogress')
                ->waitForText('Done loading...');
        });

        $this->browse(function ($browser) {
            $browser
                ->visit('/first-hide-progress')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.third')
                ->pause(500)
                ->assertMissing('#nprogress')
                ->waitForText('Done loading...');
        });
    }

    /** @test */
    function can_navigate_to_page_without_reloading()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('return window._lw_dusk_test')
                ->click('@link.to.first')
                ->waitFor('@link.to.second')
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first');
        });
    }

    /** @test */
    function can_redirect_without_reloading_from_a_page_that_was_loaded_by_wire_navigate()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('return window._lw_dusk_test')
                ->click('@redirect.to.first')
                ->waitFor('@link.to.second')
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first');
        });
    }

    /** @test */
    function can_redirect_without_reloading_using_the_helper_from_a_page_that_was_loaded_normally()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@redirect.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('return window._lw_dusk_test');
        });
    }

    /** @test */
    function can_persist_elements_across_pages()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSeeIn('@count', '1')
                ->click('@increment')
                ->assertSeeIn('@count', '2')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertSeeIn('@count', '2')
                ->click('@increment')
                ->assertSeeIn('@count', '3')
                ->assertScript('return window._lw_dusk_test');
        });
    }

    /** @test */
    function new_assets_in_head_are_loaded_and_old_ones_are_not()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first-asset')
                ->assertScript('return _lw_dusk_asset_count', 1)
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitForText('On second')
                ->assertScript('return _lw_dusk_asset_count', 1)
                ->click('@link.to.third')
                ->waitForText('On third')
                ->assertScript('return _lw_dusk_asset_count', 2);
        });
    }

    /** @test */
    function tracked_assets_reload_the_page_when_they_change()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first-tracked-asset')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertScript('return _lw_dusk_asset_count', 1)
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitForText('On second')
                ->assertScript('return window._lw_dusk_test', false)
                ->assertScript('return _lw_dusk_asset_count', 1);
        });
    }

    /** @test */
    function can_use_wire_navigate_outside_of_a_livewire_component()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first-outside')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@outside.link.to.second')
                ->waitForText('On second')
                ->assertScript('return window._lw_dusk_test');
        });
    }

    /** @test */
    function script_runs_on_initial_page_visit()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('window.foo', 'bar')
                ->assertScript('return window._lw_dusk_test');
        });
    }

    /** @test */
    function can_navigate_to_component_with_url_attribute_and_update_correctly()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/query-page')
                ->assertSee('Query: 0')
                ->click('@link.with.query.1')
                ->assertSee('Query: 1')
                ->waitForNavigate()->click('@link.with.query.2')
                ->assertSee('Query: 2');
        });
    }

    /** @test */
    function navigate_scrolls_to_top_and_back_preserves_scroll()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first-scroll')
                ->assertVisible('@first-target')
                ->assertNotInViewPort('@first-target')
                ->scrollTo('@first-target')
                ->assertInViewPort('@first-target')
                ->click('@link.to.second')
                ->waitForText('On second')
                ->assertNotInViewPort('@second-target')
                ->scrollTo('@second-target')
                ->back()
                ->assertInViewPort('@first-target')
                ->forward()
                ->assertInViewPort('@second-target')
                ;
        });
    }

    /** @test */
    public function navigate_is_only_triggered_on_left_click()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->rightClick('@link.to.second')
                ->pause(500) // Let navigate run if it was going to (it should not)
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ;
        });
    }
}

class FirstPage extends Component
{
    function redirectToPageTwoUsingNavigate()
    {
        return $this->redirect('/second', navigate: true);
    }

    function render()
    {
        return <<<'HTML'
        <div>
            <div>On first</div>

            <a href="/second" wire:navigate.hover dusk="link.to.second">Go to second page</a>
            <a href="/third" wire:navigate.hover dusk="link.to.third">Go to slow third page</a>
            <button type="button" wire:click="redirectToPageTwoUsingNavigate" dusk="redirect.to.second">Redirect to second page</button>

            @persist('foo')
                <div x-data="{ count: 1 }">
                    <span x-text="count" dusk="count"></span>
                    <button x-on:click="count++" dusk="increment">+</button>
                </div>
            @endpersist
        </div>
        HTML;
    }
}

class FirstPageWithLinkOutside extends Component {
    #[Layout('test-views::layout-with-navigate-outside')]
    function render() {
        return '<div>On first</div>';
    }
}

class SecondPage extends Component
{
    function redirectToPageOne() {
        return redirect('/first');
    }

    function render()
    {
        return <<<'HTML'
        <div>
            <div>On second</div>

            <a href="/first" wire:navigate dusk="link.to.first">Go to first page</a>
            <button type="button" wire:click="redirectToPageOne" dusk="redirect.to.first">Redirect to first page</button>

            @persist('foo')
                <div x-data="{ count: 1 }">
                    <span x-text="count" dusk="count"></span>
                    <button x-on:click="count++" dusk="increment">+</button>
                </div>
            @endpersist

            <script data-navigate-once>window.foo = 'bar';</script>
        </div>
        HTML;
    }
}

class ThirdPage extends Component
{
    public function mount()
    {
        sleep(1);
    }

    function render()
    {
        return <<<'HTML'
        <div>
            Done loading...
        </div>
        HTML;
    }
}

class FirstAssetPage extends Component {
    #[\Livewire\Attributes\Layout('test-views::layout')]
    function render() {
        return '<div>On first asset page <a href="/second-asset" wire:navigate dusk="link.to.second">Go to second page</a></div>';
    }
}

class SecondAssetPage extends Component {
    #[\Livewire\Attributes\Layout('test-views::layout')]
    function render() {
        return '<div>On second asset page <a href="/third-asset" wire:navigate dusk="link.to.third">Go to third page</a></div>';
    }
}

class ThirdAssetPage extends Component {
    #[\Livewire\Attributes\Layout('test-views::changed-layout')]
    function render() {
        return '<div>On third asset page</div>';
    }
}

class FirstTrackedAssetPage extends Component {
    #[\Livewire\Attributes\Layout('test-views::tracked-layout')]
    function render() {
        return '<div>On first asset page <a href="/second-tracked-asset" wire:navigate dusk="link.to.second">Go to second page</a></div>';
    }
}

class SecondTrackedAssetPage extends Component {
    #[\Livewire\Attributes\Layout('test-views::changed-tracked-layout')]
    function render() {
        return '<div>On second asset page</div>';
    }
}

class QueryPage extends Component
{
    #[Url]
    public $query = 0;

    public function render()
    {
        return <<<'HTML'
            <div>
                <div>Query: {{ $query }}</div>
                <a href="/query-page?query=1" dusk="link.with.query.1">Link with query 1</a>
                <a href="/query-page?query=2" wire:navigate dusk="link.with.query.2">Link with query 2</a>
            </div>
        HTML;
    }
}

class FirstScrollPage extends Component
{
    function render()
    {
        return <<<'HTML'
        <div>
            <div>On first</div>

            <div style="height: 100vh;">spacer</div>

            <div dusk="first-target">below the fold</div>

            <a href="/second-scroll" wire:navigate.hover dusk="link.to.second">Go to second page</a>

            <div style="height: 100vh;">spacer</div>
        </div>
        HTML;
    }
}

class SecondScrollPage extends Component
{
    function render()
    {
        return <<<'HTML'
        <div>
            <div>On second</div>

            <div style="height: 100vh;">spacer</div>

            <div dusk="second-target">below the fold</div>

            <div style="height: 100vh;">spacer</div>
        </div>
        HTML;
    }
}
