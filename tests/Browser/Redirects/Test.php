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
    public function it_should_not_re_render_before_redirect_when_config_should_skip_render_on_redirect_is_set_to_true()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?shouldSkipRenderOnRedirect=true')
                /*
                 * This test skips render on redirect, which means
                 * the latest render won't be written to bfcache
                 */
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
                ->waitForLivewire()->click('@redirect.button')
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'bar')
                ->pause(50)
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
            ;
        });
    }

    /** @test */
    public function it_should_re_render_before_redirect_when_config_should_skip_render_on_redirect_is_set_to_false()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?shouldSkipRenderOnRedirect=false')
                /*
                 * This test runs render on redirect, which means
                 * the latest render will be written to bfcache
                 */
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
                ->waitForLivewire()->click('@redirect.button')
                ->assertSeeIn('@redirect.blade.output', 'bar')
                ->assertSeeIn('@redirect.alpine.output', 'bar')
                ->pause(50)
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
            ;
        });
    }

    /** @test */
    public function it_should_disable_browser_cache_when_config_disable_back_button_cache_is_set_to_true()
    {
        Foo::first()->update(['name' => 'foo']);

        $this->browse(function ($browser) {
            // Need to also set should skip render to false for this test to work properly
            Livewire::visit($browser, Component::class, '?disableBackButtonCache=true&shouldSkipRenderOnRedirect=false')
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
                ->assertSeeIn('@redirect.blade.model-output', 'bar')
                ->assertSeeIn('@redirect.alpine.model-output', 'bar')
            ;
        });
    }

    /** @test */
    public function it_should_not_disable_browser_cache_when_config_disable_back_button_cache_is_set_to_false()
    {
        Foo::first()->update(['name' => 'foo']);

        $this->browse(function ($browser) {
            // Need to also set should skip render on redirect to false for this test to work properly
            Livewire::visit($browser, Component::class, '?disableBackButtonCache=false&shouldSkipRenderOnRedirect=false')
                /*
                 * Livewire response is still handled event if redirecting.
                 * (Otherwise, the browser cache after a back button press
                 * won't be up to date.)
                 */
                ->assertSeeIn('@redirect.blade.model-output', 'foo')
                ->assertSeeIn('@redirect.alpine.model-output', 'foo')
                // ->tinker()
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
