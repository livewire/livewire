<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_lazy_load_component_with_custom_layout()
    {
        Livewire::component('page', PageWithCustomLayout::class);
        Route::get('/one', PageWithCustomLayout::class)->middleware('web');

        Livewire::component('page', PageWithCustomLayoutOnView::class);
        Route::get('/two', PageWithCustomLayoutOnView::class)->middleware('web');

        Livewire::component('page', PageWithCustomLayoutAttributeOnMethod::class);
        Route::get('/three', PageWithCustomLayoutAttributeOnMethod::class)->middleware('web');

        $this->get('/one')->assertSee('This is a custom layout');
        $this->get('/two')->assertSee('This is a custom layout');
        $this->get('/three')->assertSee('This is a custom layout');
    }

    public function test_can_disable_lazy_loading_during_unit_tests()
    {
        Livewire::component('lazy-component', BasicLazyComponent::class);

        Livewire::withoutLazyLoading()->test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                    <div>
                        <livewire:lazy-component />
                    </div>
                HTML;
            }
        })
        ->assertDontSee('Loading...')
        ->assertSee('Hello world!');
    }
}

#[Lazy]
class BasicLazyComponent extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render()
    {
        return '<div>Hello world!</div>';
    }
}

#[Layout('components.layouts.custom'), Lazy]
class PageWithCustomLayout extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }
}

#[Lazy]
class PageWithCustomLayoutAttributeOnMethod extends Component {
    #[Layout('components.layouts.custom')]
    public function placeholder() {
        return '<div>Loading...</div>';
    }
}

#[Lazy]
class PageWithCustomLayoutOnView extends Component {
    public function placeholder() {
        return view('show-name', ['name' => 'foo'])->layout('components.layouts.custom');
    }
}

