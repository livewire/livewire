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

    /** @test */
    public function it_correctly_shows_flash_messages_before_and_after_direct()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /*
                 * Flashing a message shows up right away, AND
                 * will show up if you redirect to a different
                 * page right after.
                 */
                ->assertNotPresent('@flash.message')
                ->waitForLivewire()->click('@flash')
                ->assertPresent('@flash.message')
                ->waitForLivewire()->click('@refresh')
                ->assertNotPresent('@flash.message')
                ->click('@redirect-with-flash')->waitForReload()
                ->assertPresent('@flash.message')
                ->waitForLivewire()->click('@refresh')
                ->assertNotPresent('@flash.message')
            ;
        });
    }

    /** @test */
    public function it_should_disable_browser_cache_when_disable_back_button_cache_is_set_to_true()
    {
        Foo::first()->update(['name' => 'foo']);

        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?disableBackButtonCache=true')
                ->assertSeeIn('@redirect.blade.model-output', 'foo')
                ->assertSeeIn('@redirect.alpine.model-output', 'foo')

                ->waitForLivewire()->click('@redirect-with-model.button')
                // Because we are not skipping render we should see new value
                ->assertSeeIn('@redirect.blade.model-output', 'bar')
                ->assertSeeIn('@redirect.alpine.model-output', 'bar')
                ->pause(500)

                ->back()
                ->assertSeeIn('@redirect.blade.model-output', 'bar')
                ->assertSeeIn('@redirect.alpine.model-output', 'bar')
            ;
        });
    }

    /** @test */
    public function it_should_not_disable_browser_cache_when_disable_back_button_cache_is_set_to_false()
    {
        Foo::first()->update(['name' => 'foo']);

        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?disableBackButtonCache=false')
                /*
                 * Livewire response is still handled event if redirecting.
                 * (Otherwise, the browser cache after a back button press
                 * won't be up to date.)
                 */
                ->assertSeeIn('@redirect.blade.model-output', 'foo')
                ->assertSeeIn('@redirect.alpine.model-output', 'foo')
                
                ->waitForLivewire()->click('@redirect-with-model.button')
                // Because we are not skipping render we should see new value
                ->assertSeeIn('@redirect.blade.model-output', 'bar')
                ->assertSeeIn('@redirect.alpine.model-output', 'bar')
                ->pause(500)
                ->back()

                ->pause(500)
                ->assertSeeIn('@redirect.blade.model-output', 'foo')
                ->assertSeeIn('@redirect.alpine.model-output', 'foo')
            ;
        });
    }
}

class Foo extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'foo'],
    ];
}
