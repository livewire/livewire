<?php

namespace Livewire\Features\SupportNavigate;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Livewire;

class PersistBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook() {
        return function() {
            View::addNamespace('test-views', __DIR__.'/test-views');

            Livewire::component('persist-component', PersistComponent::class);
            Livewire::component('first-page', FirstPage::class);
            Livewire::component('second-page', SecondPage::class);

            Route::middleware(['web'])->group(function() {
                Route::get('/first', FirstPage::class);
                Route::get('/second', SecondPage::class);
            });
        };
    }

    /** @test */
    function can_navigate_to_page_and_handle_livewire_component_in_persist()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertSee('On first')
                ->click('@link.to.second')
                ->assertScript('return window._lw_dusk_test')
                ->waitFor('@save')
                ->assertSee('On second')
                ->click('@save')
                ->assertConsoleLogMissingWarning('Uncaught Component not found');
        });
    }
}

class PersistComponent extends Component
{
    function render(): string
    {
        return <<<'HTML'
            <div>foo</div>
        HTML;
    }
}


class FirstPage extends Component
{
    function render(): string
    {
        return <<<'HTML'
        <div>
            <h1>On first</h1>

            <a href="/second" wire:navigate dusk="link.to.second">Go to second page</a>

            @persist('foo')
                <livewire:persist-component key='persist-component'/>
            @endpersist
        </div>
        HTML;
    }
}


class SecondPage extends Component
{
    public function save(): void
    {
        //
    }

    function render(): string
    {
        return <<<'HTML'
        <div>
            <h1>On second</h1>

            <form wire:submit='save'>
                <button type='submit' dusk='save'>Save</button>
            </form>

            @persist('foo')
                <livewire:persist-component key='persist-component'/>
            @endpersist
        </div>
        HTML;
    }
}
