<?php

namespace Livewire\Features\SupportQueryString;

use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_query_string()
    {
        Livewire::visit(new class extends Component {
            #[Url]
            public $count = 0;

            public function increment(){
                $this->count++;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                     <div dusk="count">{{ $count }}</div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML,
                    [
                        'count' => $this->count,
                    ]
                );
            }
        })

        ->assertQueryStringMissing('count')
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')
        ->assertQueryStringHas('count', '1')
        ;
    }

    public function test_query_string_disabled()
    {
        Livewire::visit(new class extends Component {
            #[Url]
            public $count = 0;

            public function increment(){
                $this->count++;
            }

            protected function queryString()
            {
                return null;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                     <div dusk="count">{{ $count }}</div>
                    <button dusk='increment' wire:click='increment'></button>
                    </div>
                    HTML,
                    [
                        'count' => $this->count,
                    ]
                );
            }
        })

            ->assertQueryStringMissing('count')
            ->assertSeeIn('@count', '0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->assertQueryStringMissing('count')
        ;
    }
}
