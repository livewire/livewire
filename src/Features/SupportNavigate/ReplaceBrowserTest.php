<?php

namespace Livewire\Features\SupportNavigate;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ReplaceBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            View::addNamespace('test-views', __DIR__ . '/test-views');

            Livewire::component('replace-test-first', ReplaceTestFirstPage::class);
            Livewire::component('replace-test-second', ReplaceTestSecondPage::class);
            Livewire::component('replace-test-third', ReplaceTestThirdPage::class);
            Livewire::component('form-page-with-replace', FormPageWithReplace::class);
            Livewire::component('success-page-with-replace', SuccessPageWithReplace::class);
            Livewire::component('before-form-page', BeforeFormPage::class);
            Livewire::component('scroll-page-with-replace', ScrollPageWithReplace::class);
            Livewire::component('scroll-replace-second-page', ScrollReplaceSecondPage::class);
            Livewire::component('hover-prefetch-replace-page', HoverPrefetchReplacePage::class);
            Livewire::component('js-replace-first-page', JsReplaceFirstPage::class);
            Livewire::component('js-replace-second-page', JsReplaceSecondPage::class);

            Route::get('/replace-test-first', ReplaceTestFirstPage::class)->middleware('web');
            Route::get('/replace-test-second', ReplaceTestSecondPage::class)->middleware('web');
            Route::get('/replace-test-third', ReplaceTestThirdPage::class)->middleware('web');
            Route::get('/form-replace-test', FormPageWithReplace::class)->middleware('web');
            Route::get('/success-replace-test', SuccessPageWithReplace::class)->middleware('web');
            Route::get('/before-form', BeforeFormPage::class)->middleware('web');
            Route::get('/scroll-replace-first', ScrollPageWithReplace::class)->middleware('web');
            Route::get('/scroll-replace-second', ScrollReplaceSecondPage::class)->middleware('web');
            Route::get('/hover-replace-first', HoverPrefetchReplacePage::class)->middleware('web');
            Route::get('/hover-replace-second', ReplaceTestSecondPage::class)->middleware('web');
            Route::get('/js-replace-first', JsReplaceFirstPage::class)->middleware('web');
            Route::get('/js-replace-second', JsReplaceSecondPage::class)->middleware('web');
        };
    }

    public function test_navigate_replace_modifier_replaces_history_entry()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/replace-test-first')
                ->waitForText('On first page');

            // Get initial history length
            $initialLength = $browser->script('return window.history.length')[0];

            // Navigate to second page (should push)
            $browser
                ->click('@link.to.second')
                ->waitForText('On second page');

            // Verify history was pushed
            $afterPushLength = $browser->script('return window.history.length')[0];
            $this->assertEquals($initialLength + 1, $afterPushLength);

            // Navigate to third page with replace (should NOT push)
            $browser
                ->click('@link.to.third.replace')
                ->waitForText('On third page');

            // Verify history was replaced (same length)
            $afterReplaceLength = $browser->script('return window.history.length')[0];
            $this->assertEquals($afterPushLength, $afterReplaceLength);

            // Press back button - should skip second page
            $browser
                ->back()
                ->waitForText('On first page')
                ->assertDontSee('On second page')
                ->assertDontSee('On third page');
        });
    }

    public function test_redirect_with_replace_after_form_submission()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/before-form')
                ->waitForText('Before Form Page')
                ->click('@link.to.form')
                ->waitForText('Fill Form');

            $historyLengthBeforeSubmit = $browser->script('return window.history.length')[0];

            // Submit form (should redirect with replace)
            $browser
                ->type('@name-input', 'John Doe')
                ->click('@submit-button')
                ->waitForText('Success Page');

            // Verify history was replaced (not increased)
            $historyLengthAfterSubmit = $browser->script('return window.history.length')[0];
            $this->assertEquals($historyLengthBeforeSubmit, $historyLengthAfterSubmit);

            // Press back - should skip form page
            $browser
                ->back()
                ->waitForText('Before Form Page')
                ->assertDontSee('Fill Form');
        });
    }

    public function test_navigate_replace_with_preserve_scroll_modifier()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/scroll-replace-first')
                ->assertVisible('@scroll-target')
                ->assertNotInViewPort('@scroll-target')
                ->scrollTo('@scroll-target')
                ->assertInViewPort('@scroll-target');

            $historyLength = $browser->script('return window.history.length')[0];

            // Navigate with replace + preserve scroll
            $browser
                ->click('@link.replace.preserve')
                ->waitForText('On scroll second page');

            // Verify scroll is still down (we can't easily assert pixel perfect with just preserved, but we can check if we are not at top)
            $scrollY = $browser->script('return window.scrollY')[0];
            $this->assertGreaterThan(0, $scrollY);

            // Verify history was replaced
            $afterLength = $browser->script('return window.history.length')[0];
            $this->assertEquals($historyLength, $afterLength);
        });
    }

    public function test_navigate_replace_with_hover_prefetch()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/hover-replace-first')
                ->waitForText('Hover Prefetch Test');

            $historyLength = $browser->script('return window.history.length')[0];

            // Hover to trigger prefetch
            $browser
                ->mouseover('@link.replace.hover')
                ->pause(500) // Wait for prefetch
                ->click('@link.replace.hover')
                ->waitForText('On second page');

            // Verify history was replaced
            $afterLength = $browser->script('return window.history.length')[0];
            $this->assertEquals($historyLength, $afterLength);
        });
    }

    public function test_javascript_navigate_with_replace_option()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/js-replace-first')
                ->waitForText('JS Test First');

            $historyLength = $browser->script('return window.history.length')[0];

            // Navigate using JavaScript API with replace option
            $browser
                ->click('@navigate-button')
                ->waitForText('JS Test Second');

            // Verify history was replaced
            $afterLength = $browser->script('return window.history.length')[0];
            $this->assertEquals($historyLength, $afterLength);

            // Back button should go to page before first page
            $browser->back();
            $browser->assertDontSee('JS Test First');
        });
    }
}

class ReplaceTestFirstPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On first page</div>
            <a href="/replace-test-second" wire:navigate dusk="link.to.second">To Second (Push)</a>
        </div>
        HTML;
    }
}

class ReplaceTestSecondPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On second page</div>
            <a href="/replace-test-third" wire:navigate.replace dusk="link.to.third.replace">To Third (Replace)</a>
        </div>
        HTML;
    }
}

class ReplaceTestThirdPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On third page</div>
        </div>
        HTML;
    }
}

class BeforeFormPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Before Form Page</div>
            <a href="/form-replace-test" wire:navigate dusk="link.to.form">Go to Form</a>
        </div>
        HTML;
    }
}

class FormPageWithReplace extends Component
{
    public $name = '';

    public function submit()
    {
        return $this->redirect('/success-replace-test', navigate: true, replace: true);
    }

    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Fill Form</div>
            <input wire:model="name" dusk="name-input">
            <button wire:click="submit" dusk="submit-button">Submit</button>
        </div>
        HTML;
    }
}

class SuccessPageWithReplace extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Success Page</div>
        </div>
        HTML;
    }
}

class ScrollPageWithReplace extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Scroll Test Page</div>
            <div style="height: 2000px;"></div>
            <div dusk="scroll-target">Target Element</div>
            <!-- Pointing to second page which will be rendered plain but preserve-scroll should keep us down -->
            <a href="/scroll-replace-second" wire:navigate.replace.preserve-scroll dusk="link.replace.preserve">
                Navigate with Replace & Preserve Scroll
            </a>
        </div>
        HTML;
    }
}

class ScrollReplaceSecondPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On scroll second page</div>
            <div style="height: 2000px;"></div>
        </div>
        HTML;
    }
}

class HoverPrefetchReplacePage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Hover Prefetch Test</div>
            <a href="/hover-replace-second" wire:navigate.replace.hover dusk="link.replace.hover">
                Navigate with Replace & Hover Prefetch
            </a>
        </div>
        HTML;
    }
}

class JsReplaceFirstPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>JS Test First</div>
            <button onclick="Livewire.navigate('/js-replace-second', { replace: true })" dusk="navigate-button">
                Navigate with JS Replace
            </button>
        </div>
        HTML;
    }
}

class JsReplaceSecondPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>JS Test Second</div>
        </div>
        HTML;
    }
}
