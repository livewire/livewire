<?php

namespace Tests\Unit;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Livewire;

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
    public function redirect_helper_using_key_value_with()
    {
        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelperUsingKeyValueWith');

        $this->assertEquals(url('foo'), $component->payload['effects']['redirect']);

        $this->assertEquals('livewire-is-awesome',Session::get('success'));
    }

    /** @test */
    public function redirect_helper_using_array_with()
    {
        $component = Livewire::test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirectHelperUsingArrayWith');

        $this->assertEquals(url('foo'), $component->payload['effects']['redirect']);

        $this->assertEquals('livewire-is-awesome',Session::get('success'));
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

    /** @test */
    public function skip_render_on_redirect_by_default()
    {
        $component = Livewire::test(SkipsRenderOnRedirect::class);

        $this->assertEquals('/local', $component->payload['effects']['redirect']);
        $this->assertNull($component->payload['effects']['html']);
    }

    /** @test */
    public function dont_skip_render_on_redirect_if_config_set()
    {
        config()->set('livewire.render_on_redirect', true);

        $component = Livewire::test(SkipsRenderOnRedirect::class);

        $this->assertEquals('/local', $component->payload['effects']['redirect']);
        $this->assertStringContainsString('Render has run', $component->payload['effects']['html']);
    }

    /** @test */
    public function manually_override_dont_skip_render_on_redirect_using_skip_render_method()
    {
        config()->set('livewire.render_on_redirect', true);

        $component = Livewire::test(RenderOnRedirectWithSkipRenderMethod::class);

        $this->assertEquals('/local', $component->payload['effects']['redirect']);
        $this->assertNull($component->payload['effects']['html']);
    }

    /** @test */
    public function it_redirects_properly_even_if_persistent_middleware_feature_returns_an_empty_response()
    {
        Livewire::test(RedirectFromActionComponent::class)
            ->call('runAction')
            ->assertRedirect('/home');
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

    public function triggerRedirectHelperUsingKeyValueWith()
    {
        return redirect('foo')->with('success', 'livewire-is-awesome');
    }

    public function triggerRedirectHelperUsingArrayWith()
    {
        return redirect('foo')->with([
            'success' => 'livewire-is-awesome'
        ]);
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

class SkipsRenderOnRedirect extends Component
{
    public function mount()
    {
        return $this->redirect('/local');
    }

    public function render()
    {
        return <<<'HTML'
<div>
    Render has run
</div>
HTML;
    }
}

class RenderOnRedirectWithSkipRenderMethod extends Component
{
    public function mount()
    {
        $this->skipRender();
        return $this->redirect('/local');
    }

    public function render()
    {
        return <<<'HTML'
<div>
    Render has run
</div>
HTML;
    }
}

class RedirectFromActionComponent extends Component
{
    public function runAction()
    {
        return app(RedirectAction::class);
    }

    public function render()
    {
        return '<div></div>';
    }
}

class RedirectAction implements Responsable
{
    protected $response;

    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    public function toResponse($request)
    {
        return $this->response->redirectTo('/home');
    }
}