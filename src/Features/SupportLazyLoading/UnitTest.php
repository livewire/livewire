<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class UnitTest extends \Tests\TestCase
{
    #[Test]
    public function can_lazy_load_component_with_custom_layout()
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

