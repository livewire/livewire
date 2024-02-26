<?php

namespace Livewire\Features\SupportNavigate\Tests;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Livewire;

class BugsBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            View::addNamespace('test-views', __DIR__.'/test-views');

            Livewire::component('number-page', NumberPage::class);

            Route::get('/{number}', NumberPage::class)->middleware('web');
        };
    }

    /** @test */
    public function navigate_back_button_state_works()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/1')
                ->assertSee('On 1')

                ->tap(function ($brower) {
                    $pageChanges = 100;
                    for ($i = 2; $i <= $pageChanges; $i++) {
                        $brower->click("@link.to.{$i}")
                            ->waitForText("On {$i}")
                            ->assertPathIs("/{$i}");
                    }

                    for ($i = $pageChanges - 1; $i > 1; $i--) {
                        $brower->back()
                            ->waitForText("On {$i}")
                            ->assertPathIs("/{$i}");
                    }
                })

                ->back()
                ->waitForText('On 1')

                // ->click('@link.to.1')
                // ->waitForText('On 1')
                // ->assertPathIs('/1')

                // ->click('@link.to.3')
                // ->waitForText('On 3')
                // ->assertPathIs('/3')

                // ->click('@link.to.4')
                // ->waitForText('On 4')
                // ->assertPathIs('/4')

                // ->click('@link.to.5')
                // ->waitForText('On 5')
                // ->assertPathIs('/5')

                // ->click('@link.to.6')
                // ->waitForText('On 6')
                // ->assertPathIs('/6')

                // ->click('@link.to.7')
                // ->waitForText('On 7')
                // ->assertPathIs('/7')

                // ->click('@link.to.8')
                // ->waitForText('On 8')
                // ->assertPathIs('/8')

                // ->click('@link.to.9')
                // ->waitForText('On 9')
                // ->assertPathIs('/9')

                // ->click('@link.to.10')
                // ->waitForText('On 10')
                // ->assertPathIs('/10')

                // ->click('@link.to.11')
                // ->waitForText('On 11')
                // ->assertPathIs('/11')

                // ->click('@link.to.12')
                // ->waitForText('On 12')
                // ->assertPathIs('/12')

                // ->click('@link.to.13')
                // ->waitForText('On 13')
                // ->assertPathIs('/13')

                // ->click('@link.to.14')
                // ->waitForText('On 14')
                // ->assertPathIs('/14')

                // ->click('@link.to.15')
                // ->waitForText('On 15')
                // ->assertPathIs('/15')

                // ->back() // 14
                // ->back() // 13
                // ->back() // 12
                // ->back() // 11
                // ->back() // 10
                // ->back() // 9
                // ->back() // 8
                // ->back() // 7
                // ->back() // 6
                // ->back() // 5
                // ->back() // 4
                // ->back() // 3
                // ->back() // 2
                // ->back() // 1

                // ->waitForText('On 1')

                // ->forward()
                // ->forward()
                // ->forward()

                // ->waitForText('On 4')

                // ->back()

                // ->waitForText('On 3')

                // ->waitForLivewire()->click('@refresh')
                // ->waitForText('On 3')

                // ->forward()
                // ->waitForText('On 4')

                // ->back()
                // ->waitForText('On 3')
            ;
        });
    }
}

class NumberPage extends Component
{
    public $number;

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On {{ $number }}</div>
            <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>

            @for($i = 1; $i <= 200; $i++)
                <a href="/{{ $i }}" wire:navigate dusk="link.to.{{ $i }}">Go to {{ $i }} page</a>
            @endfor
        </div>
        HTML;
    }
}
