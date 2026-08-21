<?php

namespace Livewire\Features\SupportSession;

use Illuminate\Support\Collection;
use Livewire\Attributes\Session;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class JsonSerializationBrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['session.serialization' => 'json']);
        };
    }

    public function test_can_persist_a_collection_property_to_a_json_serialized_session()
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

            public function render() { return <<<'HTML'
            <div>
                <button dusk="button" wire:click="add">Add</button>
                <span dusk="list">{{ $list->implode(', ') }}</span>
            </div>
            HTML; }
        })
            ->assertSeeIn('@list', '')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@list', '1')
            ->refresh()
            ->assertSeeIn('@list', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@list', '1, 2')
            ;
    }
}
