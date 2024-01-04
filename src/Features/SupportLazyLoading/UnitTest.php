<?php

namespace Livewire\Features\SupportLazyLoading;

use Tests\TestComponent;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_lazy_load_component_with_custom_layout()
    {
        Livewire::component('page', PageWithCustomLayout::class);

        Route::get('/', PageWithCustomLayout::class)->lazy()->middleware('web');

        $this->withoutExceptionHandling()->get('/')
            ->assertSee('This is a custom layout');
    }
}

#[Layout('components.layouts.custom')]
class PageWithCustomLayout extends Component {
    public function mount() {
        sleep(1);
    }

    public function placeholder() {
        return <<<HTML
            <div id="loading">
                Loading...
            </div>
            HTML;
    }

    public function render() { return <<<HTML
            <div id="page">
                Hello World
            </div>
            HTML; }
}
