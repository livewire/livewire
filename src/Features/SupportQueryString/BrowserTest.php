<?php

namespace Livewire\Features\SupportQueryString;

use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_url_change()
    {
        Livewire::visit(new class extends Component {

            #[Url]
            public $value = 1;

            public function increment(){
                $this->value++;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML
                );
            }
        })

        ->assertQueryStringMissing('value')
        ->waitForLivewire()->click('@increment')
        ->assertQueryStringHas('value', '2');
    }
    public function test_url_init()
    {
        Livewire::withQueryParams(['value' => '2'])->visit(new class extends Component {

            #[Url]
            public $value = 1;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>{{$value}}</div>
                    HTML,
                    [
                        'value' => $this->value,
                    ]
                );
            }
        })
            ->assertSee('2');
    }
    public function test_url_disabled_by_query_string_param_change()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child :query-string='false' />
            </div>
            HTML; }
        }, 'child' => new class extends Component {

            #[Url]
            public $value = 1;

            public function increment(){
                $this->value++;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML
                );
            }
        }])
            ->assertQueryStringMissing('value')
            ->waitForLivewire()->click('@increment')
            ->assertQueryStringMissing('value');
    }
    public function test_url_disabled_by_query_string_param_init()
    {
        Livewire::withQueryParams(['value' => '2'])->visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child :query-string='false' />
            </div>
            HTML; }
        }, 'child' => new class extends Component {

            #[Url]
            public $value = 1;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>{{$value}}</div>
                    HTML,
                    [
                        'value' => $this->value,
                    ]
                );
            }
        }])
            ->assertSee('1');
    }
    public function test_url_disabled_by_query_string_attribute_change()
    {
        Livewire::visit(new #[\Livewire\Attributes\QueryString(false)] class extends Component {

            #[Url]
            public $value = 1;

            public function increment(){
                $this->value++;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML
                );
            }
        })
            ->assertQueryStringMissing('value')
            ->waitForLivewire()->click('@increment')
            ->assertQueryStringMissing('value');
    }
    public function test_url_disabled_by_query_string_attribute_init()
    {
        Livewire::withQueryParams(['value' => '2'])->visit(new #[\Livewire\Attributes\QueryString(false)] class extends Component {

            #[Url]
            public $value = 1;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>{{$value}}</div>
                    HTML,
                    [
                        'value' => $this->value,
                    ]
                );
            }
        })
            ->assertSee('1');
    }
    public function test_url_enabled_by_query_string_param_override_change()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child :query-string='true' />
            </div>
            HTML; }
        }, 'child' => new #[\Livewire\Attributes\QueryString(false)] class extends Component {

            #[Url]
            public $value = 1;

            public function increment(){
                $this->value++;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML
                );
            }
        }])
            ->assertQueryStringMissing('value')
            ->waitForLivewire()->click('@increment')
            ->assertQueryStringHas('value', '2');
    }
    public function test_url_enabled_by_query_string_param_override_init()
    {
        Livewire::withQueryParams(['value' => '2'])->visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child :query-string='true' />
            </div>
            HTML; }
        }, 'child' => new #[\Livewire\Attributes\QueryString(false)] class extends Component {

            #[Url]
            public $value = 1;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>{{$value}}</div>
                    HTML,
                    [
                        'value' => $this->value,
                    ]
                );
            }
        }])
            ->assertSee('2');
    }
}
