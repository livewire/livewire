<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

class RedirectTest extends TestCase
{
    /** @test */
    function validate_component_properties_from_local_redirect()
    {
        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirect');

        $this->assertEquals('/local', $component->redirectTo);
    }

    /** @test */
    function validate_component_properties_from_laravel_redirect_helper()
    {
        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelper');

        $this->assertEquals(url('illuminate'), $component->redirectTo);
    }

    /** @test */
    function validate_component_properties_from_laravel_redirect_facade_using_to()
    {
        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectFacadeUsingTo');

        $this->assertEquals(url('illuminate'), $component->redirectTo);
    }

    /** @test */
    function validate_component_properties_from_laravel_redirect_facade_using_route()
    {
        $this->registerNamedRoute();

        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectFacadeUsingRoute');

        $this->assertEquals(route('illuminate'), $component->redirectTo);
    }

    /** @test */
    function validate_component_properties_from_laravel_redirect_helper_using_route()
    {
        $this->registerNamedRoute();

        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelperUsingRoute');

        $this->assertEquals(route('illuminate'), $component->redirectTo);
    }

    protected function registerNamedRoute()
    {
        Route::get('illuminate', function () {
            return true;
        })->name('illuminate');
    }
}

class TriggersRedirectStub extends Component
{
    public function triggerRedirect()
    {
        return $this->redirect('/local');
    }

    public function triggerRedirectHelper()
    {
        return redirect('illuminate');
    }

    public function triggerRedirectFacadeUsingTo()
    {
        return Redirect::to('illuminate');
    }

    public function triggerRedirectFacadeUsingRoute()
    {
        return Redirect::route('illuminate');
    }

    public function triggerRedirectHelperUsingRoute()
    {
        return redirect()->route('illuminate');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}