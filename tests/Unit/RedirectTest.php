<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

class RedirectTest extends TestCase
{
    /** @test */
    public function standard_redirect()
    {
        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirect');

        $this->assertEquals('/local', $component->payload['effects']['redirect']);
    }

    /** @test */
    public function standard_redirect_on_mount()
    {
        $component = Livewire::test(TriggersRedirectOnMountStub::class);

        $this->assertEquals('/local', $component->payload['effects']['redirect']);
    }

    /** @test */
    public function route_redirect()
    {
        $this->registerNamedRoute();

        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectRoute');

        $this->assertEquals('http://localhost/foo', $component->payload['effects']['redirect']);
    }

    /** @test */
    public function action_redirect()
    {
        $this->registerAction();

        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectAction');

        $this->assertEquals('http://localhost/foo', $component->payload['effects']['redirect']);
    }

    /** @test */
    public function redirect_helper()
    {
        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelper');

        $this->assertEquals(url('foo'), $component->payload['effects']['redirect']);
    }

    /** @test */
    public function redirect_facade_with_to_method()
    {
        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectFacadeUsingTo');

        $this->assertEquals(url('foo'), $component->payload['effects']['redirect']);
    }

    /** @test */
    public function redirect_facade_with_route_method()
    {
        $this->registerNamedRoute();

        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectFacadeUsingRoute');

        $this->assertEquals(route('foo'), $component->payload['effects']['redirect']);
    }

    /** @test */
    public function redirect_helper_with_route_method()
    {
        $this->registerNamedRoute();

        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelperUsingRoute');

        $this->assertEquals(route('foo'), $component->payload['effects']['redirect']);
    }

    /** @test */
    public function redirect_helper_with_away_method()
    {
        $this->registerNamedRoute();

        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelperUsingAway');

        $this->assertEquals(route('foo'), $component->payload['effects']['redirect']);
    }

    protected function registerNamedRoute()
    {
        Route::get('foo', function () {
            return true;
        })->name('foo');
    }

    protected function registerAction()
    {
        Route::get('foo', 'HomeController@index')->name('foo');
    }
}

class TriggersRedirectStub extends Component
{
    public function triggerRedirect()
    {
        return $this->redirect('/local');
    }

    public function triggerRedirectRoute()
    {
        return $this->redirectRoute('foo');
    }

    public function triggerRedirectAction()
    {
        return $this->redirectAction('HomeController@index');
    }

    public function triggerRedirectHelper()
    {
        return redirect('foo');
    }

    public function triggerRedirectFacadeUsingTo()
    {
        return Redirect::to('foo');
    }

    public function triggerRedirectFacadeUsingRoute()
    {
        return Redirect::route('foo');
    }

    public function triggerRedirectHelperUsingRoute()
    {
        return redirect()->route('foo');
    }

    public function triggerRedirectHelperUsingAway()
    {
        return redirect()->away('foo');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class TriggersRedirectOnMountStub extends Component
{
    public function mount()
    {
        $this->redirect('/local');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
