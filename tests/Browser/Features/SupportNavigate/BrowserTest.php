<?php

namespace Tests\Browser\Features\SupportNavigate;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportNavigate\FirstPage;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function() {
            Livewire::component('page', Page::class);
            Route::get('/page', Page::class)->middleware('web');
        };
    }

    /** @test */
    function can_navigate_to_page_with_url_attribute_and_update_correctly()
    {
        $this->browse(function ($browser) {
           $browser
               ->visit('/page')
               ->assertSee('Query: 0')
               ->click('@link.with.query.1')
               ->assertSee('Query: 1')
               ->click('@link.with.query.2')
               ->assertSee('Query: 2');
        });
    }
}

class Page extends Component
{
    #[Url]
    public $query = 0;

    public function render()
    {
        return <<<'HTML'
            <div>
                <div>Query: {{ $query }}</div>
                <a href="/page?query=1" dusk="link.with.query.1">Link with query 1</a>
                <a href="/page?query=2" wire:navigate dusk="link.with.query.2">Link with query 2</a>
            </div>
        HTML;
    }
}

