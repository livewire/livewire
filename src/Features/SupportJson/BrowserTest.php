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

    public function test_multiple_json_methods_with_mixed_success_and_errors()
    {
        Livewire::visit(new class extends Component {
            public $successResult = '';
            public $errorStatus = '';
            public $errorMessages = '';

            #[Json]
            public function successMethod()
            {
                return ['status' => 'success'];
            }

            #[Json]
            public function errorMethod()
            {
                Validator::make(['email' => ''], ['email' => 'required|email'])->validate();

                return ['status' => 'should not see this'];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call-both" @click="
                        Promise.all([
                            $wire.successMethod()
                                .then(data => { $wire.successResult = JSON.stringify(data) })
                                .catch(e => {}),
                            $wire.errorMethod()
                                .then(data => {})
                                .catch(e => { $wire.errorStatus = e.status; $wire.errorMessages = JSON.stringify(e.errors) })
                        ])
                    ">Call Both</button>

                    <span dusk="success-result" wire:text="successResult"></span>
                    <span dusk="error-status" wire:text="errorStatus"></span>
                    <span dusk="error-messages" wire:text="errorMessages"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call-both')
        ->waitForTextIn('@success-result', 'success')
        ->waitForTextIn('@error-status', '422')
        ->assertSeeIn('@success-result', '{"status":"success"}')
        ->assertSeeIn('@error-status', '422')
        ->assertSeeIn('@error-messages', 'email')
        ;
    }

    public function test_json_method_with_try_catch_pattern()
    {
        Livewire::visit(new class extends Component {
            public $result = '';
            public $errorMessage = '';

            #[Json]
            public function riskyMethod($shouldFail)
            {
                if ($shouldFail) {
                    Validator::make(['name' => ''], ['name' => 'required'])->validate();
                }

                return ['success' => true];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="call-success" @click="
                        (async () => {
                            try {
                                let data = await $wire.riskyMethod(false);
                                $wire.result = JSON.stringify(data);
                            } catch (e) {
                                $wire.errorMessage = 'error: ' + e.status;
                            }
                        })()
                    ">Call Success</button>

                    <button dusk="call-fail" @click="
                        (async () => {
                            try {
                                let data = await $wire.riskyMethod(true);
                                $wire.result = JSON.stringify(data);
                            } catch (e) {
                                $wire.errorMessage = 'error: ' + e.status;
                            }
                        })()
                    ">Call Fail</button>

                    <span dusk="result" wire:text="result"></span>
                    <span dusk="error" wire:text="errorMessage"></span>
                </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        ->click('@call-success')
        ->waitForTextIn('@result', 'success')
        ->assertSeeIn('@result', '{"success":true}')
        ->assertDontSeeIn('@error', 'error: 422')
        ->click('@call-fail')
        ->waitForTextIn('@error', '422')
        ->assertSeeIn('@error', 'error: 422')
        ;
    }
}
