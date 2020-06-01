<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Support\Facades\Route;

class TestableLivewireTestingLivewireRouteTest extends TestCase
{
    /** @test */
    public function testing_livewire_route_works_with_user_route_with_the_same_signature()
    {
        $this->expectNotToPerformAssertions();

        Route::get('/{param1}/{param2}', function() {
            return 'Livewire is awesome!';
        });

        app(LivewireManager::class)->test(TestingLivewireRouteComponent::class);
    }
}

class TestingLivewireRouteComponent extends Component
{
    public function render()
    {
        return view('null-view');
    }
}
