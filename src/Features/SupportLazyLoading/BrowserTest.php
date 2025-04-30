<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Support\Facades\Route;
use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class BrowserTest extends BrowserTestCase
{
    public function test_can_lazy_load_a_component()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() {
                sleep(1);
            }

            public function render() {
                return <<<HTML
                <div id="child">
                    Child!
                </div>
                HTML;
            }
        }])
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertSee('Child!')
        ;
    }

    public function test_can_lazy_load_a_component_on_intersect_outside_viewport()
    {
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<HTML
            <div>
                <div style="height: 200vh"></div>
                <livewire:child lazy="on-load" />
            </div>
            HTML;
            }
        }, 'child' => new class extends Component {
            public function mount()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<HTML
                <div id="child">
                    Child!
                </div>
                HTML;
            }
        }])
            ->assertDontSee('Child!')
            ->waitFor('#child')
            ->assertSee('Child!');
    }

    public function test_cant_lazy_load_a_component_on_intersect_outside_viewport()
    {
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<HTML
            <div>
                <div style="height: 200vh"></div>
                <livewire:child lazy />
            </div>
            HTML;
            }
        }, 'child' => new class extends Component {
            public function mount()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<HTML
                <div id="child">
                    Child!
                </div>
                HTML;
            }
        }])
            ->assertDontSee('Child!')
            ->pause(2000)
            ->assertDontSee('Child!');
    }

    public function can_lazy_load_full_page_component_using_attribute()
    {
        Livewire::visit(new #[\Livewire\Attributes\Lazy] class extends Component {
            public function mount() {
                sleep(1);
            }

            public function placeholder() { return <<<HTML
                <div id="loading">
                    Loading...
                </div>
                HTML; }

            public function render() { return <<<HTML
                <div id="page">
                    Hello World
                </div>
                HTML; }
        })
        ->assertSee('Loading...')
        ->assertDontSee('Hello World')
        ->waitFor('#page')
        ->assertDontSee('Loading...')
        ->assertSee('Hello World')
        ;
    }

    public function test_can_lazy_load_component_using_route()
    {
        $this->beforeServingApplication(function() {
            Livewire::component('page', Page::class);
            Route::get('/', Page::class)->lazy()->middleware('web');
        });

        $this->browse(function ($browser) {
            $browser
                ->visit('/')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('Loading...')
                ->assertDontSee('Hello World')
                ->waitFor('#page')
                ->assertDontSee('Loading...')
                ->assertSee('Hello World');
        });
    }

    public function test_can_lazy_load_a_component_with_a_placeholder()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() { sleep(1); }
            public function placeholder() { return <<<HTML
                <div id="loading">
                    Loading...
                </div>
                HTML; }
            public function render() { return <<<HTML
            <div id="child">
                Child!
            </div>
            HTML; }
        }])
        ->assertSee('Loading...')
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertDontSee('Loading...')
        ->assertSee('Child!')
        ;
    }

    public function test_can_pass_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->assertSee('Count: 1')
        ;
    }

    public function test_can_pass_props_to_mount_method_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $count;
            public function mount($count) { $this->count = $this->count + 2; }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->assertSee('Count: 3')
        ;
    }

    public function test_can_pass_reactive_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function inc() { $this->count++; }
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
                <button wire:click="inc" dusk="button">+</button>
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            #[Reactive]
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->waitForText('Count: 1')
        ->assertSee('Count: 1')
        ->waitForLivewire()->click('@button')
        ->waitForText('Count: 2')
        ->assertSee('Count: 2')
        ->waitForLivewire()->click('@button')
        ->waitForText('Count: 3')
        ->assertSee('Count: 3')
        ;
    }

    public function test_can_access_component_parameters_in_placeholder_view()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child my-parameter="A Parameter Value" lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() {
                sleep(1);
            }
            public function placeholder(array $params = []) {
                return view('placeholder', $params);
            }
            public function render() {
                return <<<HTML
                <div id="child">
                    Child!
                </div>
                HTML;
            }
        }])
        ->waitFor('#loading')
        ->assertSee('A Parameter Value')
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertSee('Child!')
        ;
    }

}

class Page extends Component {
    public function mount() {
        sleep(1);
    }

    public function placeholder() { return <<<HTML
            <div id="loading">
                Loading...
            </div>
            HTML; }

    public function render() { return <<<HTML
            <div id="page">
                Hello World
            </div>
            HTML; }
}
