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
                    <button dusk="call" @click="$wire.getData().then(([data, errors]) => { $wire.result = JSON.stringify(data) })">Call</button>

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

    public function test_json_method_returns_validation_errors()
    {
        Livewire::visit(new class extends Component {
            public $result = '';
            public $errors = '';

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
                    <button dusk="call" @click="$wire.saveData().then(([data, errors]) => { $wire.result = JSON.stringify(data); $wire.errors = JSON.stringify(errors) })">Call</button>

                    <span dusk="result" wire:text="result"></span>
                    <span dusk="errors" wire:text="errors"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call')
        ->waitForTextIn('@errors', 'name')
        ->assertSeeIn('@result', 'null')
        ->assertSeeIn('@errors', 'name')
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
}
