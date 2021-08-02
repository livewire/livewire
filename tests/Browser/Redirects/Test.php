<?php

namespace Tests\Browser\Redirects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    use RefreshDatabase;
    
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tinker()
                // /**
                //  * Flashing a message shows up right away, AND
                //  * will show up if you redirect to a different
                //  * page right after.
                //  */
                // ->assertNotPresent('@flash.message')
                // ->waitForLivewire()->click('@flash')
                // ->assertPresent('@flash.message')
                // ->waitForLivewire()->click('@refresh')
                // ->assertNotPresent('@flash.message')
                // ->click('@redirect-with-flash')->waitForReload()
                // ->assertPresent('@flash.message')
                // ->waitForLivewire()->click('@refresh')
                // ->assertNotPresent('@flash.message')

                /**
                 * Livewire response is still handled event if redirecting.
                 * (Otherwise, the browser cache after a back button press
                 * won't be up to date.)
                 */
                // ->refresh()
                // ->assertSeeIn('@redirect.blade.output', 'foo')
                // ->assertSeeIn('@redirect.alpine.output', 'foo')
                // // ->runScript('window.addEventListener("beforeunload", e => { e.preventDefault(); e.returnValue = ""; });')
                // // ->tinker()
                // ->waitForLivewire()->click('@redirect.button')
                // ->tinker()
                // ->assertSeeIn('@redirect.blade.output', 'bar')
                // ->pause(500)
                // // ->dismissDialog()
                // ->assertSeeIn('@redirect.blade.output', 'foo')
                // ->assertSeeIn('@redirect.alpine.output', 'foo')
            ;
        });
    }
}

class Foo extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'foo2'],
    ];
}
