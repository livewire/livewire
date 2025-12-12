<?php

namespace Livewire\Features\SupportJson;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Json;
use Illuminate\Support\Facades\Validator;

class BrowserTest extends BrowserTestCase
{
    public function test_can_call_json_method_and_receive_return_value()
    {
        Livewire::visit(new class extends Component {
            public $result = '';

            #[Json]
            public function getData()
            {
                return ['foo' => 'bar'];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call" @click="$wire.getData().then(data => { $wire.result = JSON.stringify(data) })">Call</button>

                    <span dusk="result" wire:text="result"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call')
        ->waitForTextIn('@result', '{"foo":"bar"}')
        ->assertSeeIn('@result', '{"foo":"bar"}')
        ;
    }

    public function test_json_method_rejects_with_validation_errors()
    {
        Livewire::visit(new class extends Component {
            public $status = '';
            public $validationErrors = '';

            #[Json]
            public function saveData()
            {
                Validator::make(['name' => ''], ['name' => 'required'])->validate();

                return ['success' => true];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call" @click="$wire.saveData().catch(e => { $wire.status = e.status; $wire.validationErrors = JSON.stringify(e.errors) })">Call</button>

                    <span dusk="status" wire:text="status"></span>
                    <span dusk="validationErrors" wire:text="validationErrors"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call')
        ->waitForTextIn('@status', '422')
        ->assertSeeIn('@status', '422')
        ->assertSeeIn('@validationErrors', 'name')
        ;
    }

    public function test_json_method_skips_render()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Json]
            public function getData()
            {
                $this->count++;

                return ['count' => $this->count];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call" @click="$wire.getData()">Call</button>

                    <span dusk="count">{{ $count }}</span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@call')
        ->assertSeeIn('@count', '0')
        ;
    }

    public function test_json_method_can_return_primitive_values()
    {
        Livewire::visit(new class extends Component {
            public $result = '';

            #[Json]
            public function getString()
            {
                return 'hello world';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call" @click="$wire.getString().then(data => { $wire.result = data })">Call</button>

                    <span dusk="result" wire:text="result"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call')
        ->waitForTextIn('@result', 'hello world')
        ->assertSeeIn('@result', 'hello world')
        ;
    }
}
