<?php

namespace Livewire\V4\Routes;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

use function Livewire\invade;

class RoutingMacroUnitTest extends TestCase
{
    public function test_macro_is_registered()
    {
        $this->assertTrue(Route::hasMacro('livewire'));
    }

    public function test_macro_can_be_used_to_define_routes()
    {
        invade(app('livewire.resolver'))->defaultViewPaths[] = __DIR__.'/fixtures/components';
        Livewire::namespace('pages', __DIR__.'/fixtures/pages');

        Route::livewire('/class-component', ClassComponent::class);
        Route::livewire('/view-component', 'view-component');
        Route::livewire('/namespaced-view-component', 'pages::namespaced-view-component');

        $this->get('/class-component')->assertSee('Class based component');
        $this->get('/view-component')->assertSee('View component');
        $this->get('/namespaced-view-component')->assertSee('Namespaced view component');
    }
}

class ClassComponent extends Component
{
    public function render()
    {
        return '<div>Class based component</div>';
    }
}
