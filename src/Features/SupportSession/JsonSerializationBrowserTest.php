<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Collection;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class JsonSerializationBrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['session.serialization' => 'json']);
        };
    }

    public function test_collection_properties_survive_a_full_page_refresh()
    {
        Livewire::visit(new class extends Component {
            #[Session]
            public ?Collection $list = null;

            public function mount(): void
            {
                $this->list ??= collect();
            }

            public function add(): void
            {
                $this->list->push($this->list->count() + 1);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="serialization">{{ config('session.serialization') }}</span>
                    <button dusk="button" wire:click="add">Add</button>
                    <span dusk="list">{{ $list->implode(', ') }}</span>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@serialization', 'json')
            ->assertSeeIn('@list', '')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@list', '1')
            ->refresh()
            ->assertSeeIn('@list', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@list', '1, 2');
    }
}
