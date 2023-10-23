<?php

namespace Livewire\Features\SupportComputed;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;
use Illuminate\Support\Facades\Cache;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_persist_computed_between_requests_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $thing = 'hey';

            #[Computed(persist: true, tags: ['foo'])]
            public function foo() {
                $this->count++;

                return 'bar';
            }

            function deleteCachedTags() {
                if (Cache::supportsTags()) {
                    Cache::tags(['foo'])->flush();
                }
            }

            function unset()
            {
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="unset" dusk="unset">unset</button>
                    <button wire:click="deleteCachedTags" dusk="deleteCachedTags">deleteCachedTags</button>

                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@unset')
        ->assertSeeIn('@count', '2')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '2');
        ->waitForLivewire()->click('@deleteCachedTags')
        ->assertSeeIn('@count', Cache::supportsTags()?'3':'2')
    }

    /** @test */
    public function can_cache_computed_properties_for_all_components_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Computed(cache: true, tags: ['foo'])]
            public function foo() {
                return $this->count;
            }

            function deleteCachedTags() {
                $this->count++;
                if (Cache::supportsTags()) {
                    Cache::tags(['foo'])->flush();
                }
            }

            function increment()
            {
                $this->count++;
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="increment" dusk="increment">unset</button>
                    <button wire:click="deleteCachedTags" dusk="deleteCachedTags">deleteCachedTags</button>

                    <div dusk="count">{{ $this->foo }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')
        ->refresh()
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@deleteCachedTags')
        ->assertSeeIn('@count', Cache::supportsTags()?'2':'1');
    }
}
