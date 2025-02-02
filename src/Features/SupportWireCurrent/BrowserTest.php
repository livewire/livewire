<?php

namespace Livewire\Features\SupportWireCurrent;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Layout;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            View::addNamespace('test-views', __DIR__ . '/test-views');

            Livewire::component('first-page', FirstPage::class);
            Livewire::component('second-page', SecondPage::class);

            Route::get('/first', FirstPage::class)->middleware('web')->name('first');
            Route::get('/first/sub', FirstSubPage::class)->middleware('web')->name('first.sub');
            Route::get('/second', SecondPage::class)->middleware('web')->name('second');
        };
    }

    public function test_wire_current_matches_urls_properly()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->waitForText('On first')

                ->assertAttributeContains('@link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@link.second', 'class', 'active')
                ->assertAttributeContains('@link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@link.second.exact', 'class', 'active')
                ->assertAttributeContains('@link.first.slash', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.slash.strict', 'class', 'active')

                ->assertAttributeContains('@route.link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.second', 'class', 'active')
                ->assertAttributeContains('@route.link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.second.exact', 'class', 'active')

                ->assertAttributeContains('@native.link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.second', 'class', 'active')
                ->assertAttributeContains('@native.link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.second.exact', 'class', 'active')

                ->click('@link.sub')
                ->waitForText('On sub')

                ->assertAttributeContains('@link.first', 'class', 'active')
                ->assertAttributeContains('@link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.exact', 'class', 'active')
                ->assertAttributeContains('@link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@link.second.exact', 'class', 'active')
                ->assertAttributeContains('@link.first.slash', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.slash.strict', 'class', 'active')

                ->assertAttributeContains('@route.link.first', 'class', 'active')
                ->assertAttributeContains('@route.link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.first.exact', 'class', 'active')
                ->assertAttributeContains('@route.link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.second.exact', 'class', 'active')

                ->assertAttributeContains('@native.link.first', 'class', 'active')
                ->assertAttributeContains('@native.link.sub', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.first.exact', 'class', 'active')
                ->assertAttributeContains('@native.link.sub.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.second.exact', 'class', 'active')

                ->click('@link.second')
                ->waitForText('On second')

                ->assertAttributeDoesntContain('@link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@link.sub', 'class', 'active')
                ->assertAttributeContains('@link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@link.sub.exact', 'class', 'active')
                ->assertAttributeContains('@link.second.exact', 'class', 'active')

                ->assertAttributeDoesntContain('@route.link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.sub', 'class', 'active')
                ->assertAttributeContains('@route.link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@route.link.sub.exact', 'class', 'active')
                ->assertAttributeContains('@route.link.second.exact', 'class', 'active')

                ->assertAttributeDoesntContain('@native.link.first', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.sub', 'class', 'active')
                ->assertAttributeContains('@native.link.second', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.first.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@native.link.sub.exact', 'class', 'active')
                ->assertAttributeContains('@native.link.second.exact', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.slash', 'class', 'active')
                ->assertAttributeDoesntContain('@link.first.slash.strict', 'class', 'active')
                ;
        });
    }

    public function test_wire_current_supports_adding_data_current_attribute()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->waitForText('On first')

                ->assertAttribute('@link.first', 'data-current', '')
                ->assertAttributeMissing('@link.sub', 'data-current', '')
                ->assertAttributeMissing('@link.second', 'data-current', '')
                ->assertAttribute('@link.first.exact', 'data-current', '')
                ->assertAttributeMissing('@link.sub.exact', 'data-current', '')
                ->assertAttributeMissing('@link.second.exact', 'data-current', '')

                ->click('@link.sub')
                ->waitForText('On sub')

                ->assertAttribute('@link.first', 'data-current', '')
                ->assertAttribute('@link.sub', 'data-current', '')
                ->assertAttributeMissing('@link.second', 'data-current', '')
                ->assertAttributeMissing('@link.first.exact', 'data-current', '')
                ->assertAttribute('@link.sub.exact', 'data-current', '')
                ->assertAttributeMissing('@link.second.exact', 'data-current', '')

                ->click('@link.second')
                ->waitForText('On second')

                ->assertAttributeMissing('@link.first', 'data-current', '')
                ->assertAttributeMissing('@link.sub', 'data-current', '')
                ->assertAttribute('@link.second', 'data-current', '')
                ->assertAttributeMissing('@link.first.exact', 'data-current', '')
                ->assertAttributeMissing('@link.sub.exact', 'data-current', '')
                ->assertAttribute('@link.second.exact', 'data-current', '')
                ;
        });
    }
}

#[Layout('test-views::navbar-sidebar')]
class FirstPage extends Component
{
    public function doRedirect($to)
    {
        return $this->redirect($to, navigate: true);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On first</div>
        </div>
        HTML;
    }
}

class FirstSubPage extends Component
{
    #[Layout('test-views::navbar-sidebar')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On sub</div>
        </div>
        HTML;
    }
}

class SecondPage extends Component
{
    #[Layout('test-views::navbar-sidebar')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On second</div>
        </div>
        HTML;
    }
}
