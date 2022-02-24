<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;

class TestableLivewireDoesNotOverwriteAppRouteTest extends TestCase
{
    /** @test */
    public function custom_set_route_persists_through_testable()
    {
        Router::any('/some/user/{user_id}', RouteParamFetcher::class);

        $this->create();

        $component = Livewire::test(RouteParamFetcher::class);

        $component->assertSet('user_id', 1);
    }

    /** @test */
    public function standard_component_testing_works()
    {
        $component = Livewire::test(StandardComponent::class);

        $component->assertOk();
    }

    private function create(string $class = Request::class)
    {
        $request = $class::create('/some/user/1');
        $request->setRouteResolver(function() use ($request) { return (new Route('GET', '/some/user/{user_id}', []))->bind($request); });
        // Override the requst() helper (which calls app('request')) to return the newly created route
        app()->bind('request', function() use ($request) { return $request; });
        return $request;
    }
}

class RouteParamFetcher extends Component
{
    public $user_id;

    public function mount()
    {
        $parameters = request()->route()->parameters();

        throw_if(!in_array('user_id', array_keys($parameters)), 'Exception', 'user_id not passed through route');

        $this->user_id = request()->route()->parameter('user_id');
    }

    public function render()
    {
        return view('null-view');
    }
}

class StandardComponent extends Component
{
    public $user_id;

    public function render()
    {
        return view('null-view');
    }
}
