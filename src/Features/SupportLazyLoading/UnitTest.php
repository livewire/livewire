<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;

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

    public function test_a_lazy_component_loads_with_its_own_mount_params()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);

        $html = html_entity_decode(Livewire::mount('lazy-alpha', ['level' => 5]));
        preg_match("/__lazyLoad\('([^']+)'\)/", $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        Livewire::test('lazy-alpha')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5');
    }

    public function test_a_lazy_component_registers_its_listeners_only_once_it_has_loaded()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-with-listener', LazyWithListener::class);

        $component = Livewire::test('lazy-with-listener');

        // The placeholder is dormant, so the browser has nothing to dispatch to yet...
        $this->assertArrayNotHasKey('listeners', $component->effects);

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $component->call('__lazyLoad', $matches[1]);

        // ...and the resume response is what registers them...
        $this->assertEquals(['refresh-child'], $component->effects['listeners']);
    }

    public function test_a_repeat_lazy_load_call_is_ignored_after_the_component_has_loaded()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);

        $component = Livewire::test('lazy-alpha', ['level' => 5]);

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $component
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->assertSee('mounts:1')
            ->call('__lazyLoad', $matches[1])
            ->assertSee('level:5')
            ->assertSee('mounts:1');
    }

    public function test_a_lazy_load_call_on_a_non_lazy_component_is_not_claimed()
    {
        $this->expectException(MethodNotFoundException::class);

        Livewire::test(new class extends Component {
            public function render() {
                return '<div></div>';
            }
        })->call('__lazyLoad', 'invalid');
    }

    public function test_a_mount_params_container_is_scoped_to_its_own_component()
    {
        SupportLazyLoading::$disableWhileTesting = false;

        Livewire::component('lazy-alpha', LazyAlpha::class);
        Livewire::component('lazy-beta', LazyBeta::class);

        $html = html_entity_decode(Livewire::mount('lazy-alpha', ['level' => 1]));
        preg_match("/__lazyLoad\('([^']+)'\)/", $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        $this->expectException(CorruptComponentPayloadException::class);

        Livewire::test('lazy-beta')->call('__lazyLoad', $matches[1]);
    }
}

#[Lazy]
class LazyAlpha extends Component {
    public $level = 0;
    public $mounts = 0;

    public function mount($level = 0) {
        $this->level = $level;
        $this->mounts++;
    }

    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render() {
        return '<div>level:'.$this->level.' mounts:'.$this->mounts.'</div>';
    }
}

#[Lazy]
class LazyWithListener extends Component {
    public $count = 0;

    #[On('refresh-child')]
    public function refresh() {
        $this->count++;
    }

    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render() {
        return '<div>count:'.$this->count.'</div>';
    }
}

#[Lazy]
class LazyBeta extends Component {
    public function placeholder() {
        return '<div>Loading...</div>';
    }

    public function render() {
        return '<div>beta</div>';
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

