<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Component;
use Livewire\Features\SupportQueryString\BaseUrl;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    function test_debounce_attribute_delays_live_model_network_update()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(250)]
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@text', 'livewire') // wire:text should update immediately
            ->pause(300) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    function test_blade_debounce_modifier_overrides_attribute()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(1000)]
            public $foo = '';

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.debounce.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="client"></span>
                    <span dusk="server">{{ $foo }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@server', '')
            ->assertSeeIn('@client', 'livewire') // wire:text should update immediately
            ->pause(400) // Wait for the request to be handled
            ->assertSeeIn('@server', 'livewire')
            ->assertScript('window.requests', 1); // Only one request was sent
    }

    function test_debounce_attribute_does_not_force_live_on_deferred_model()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(250)]
            public $foo = '';

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model="foo" dusk="input">
                    <span wire:text="foo" dusk="client"></span>
                    <span dusk="server">{{ $foo }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@client', 'livewire') // wire:text should update immediately
            ->pause(300) // Wait for requests to be handled
            ->assertSeeIn('@server', '') // server value should not updated
            ->assertScript('window.requests', 0); 
    }

    function test_only_annotated_property_uses_attribute_debounce()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(500)]
            public $slow = '';

            public $fast = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="slow" dusk="slow" />
                    <input type="text" wire:model.live="fast" dusk="fast" />
                    <span dusk="slow-out">{{ $slow }}</span>
                    <span dusk="fast-out">{{ $fast }}</span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@fast', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@fast-out', 'livewire')
            ->assertSeeIn('@slow-out', '')
            ->typeSlowly('@slow', 'livewire', 50)
            ->assertSeeIn('@slow-out', '')
            ->waitForTextIn('@slow-out', 'livewire') // 5s clearly exceeds 500ms debounce duration
        ;
    }

    public function test_debounce_attribute_works_on_multiple_back_forward_navigations()
    {
        Livewire::visit(new class extends Component {
            #[BaseUrl(history: true)]
            public $page = '1';

            #[BaseDebounce(500)]
            public $search = '';

            public function setPage($value)
            {
                $this->page = $value;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="text" wire:model.live="search" dusk="input" />
                        <button dusk="page2" wire:click="setPage('2')">Page 2</button>
                        <button dusk="page3" wire:click="setPage('3')">Page 3</button>
                        <button dusk="page4" wire:click="setPage('4')">Page 4</button>
                        <span dusk="output">{{ $search }}</span>
                        <span dusk="page">{{ $page }}</span>
                    </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@output', '')
            ->assertSeeIn('@page', '1')

            // Initial debounce
            ->type('@input', 'one')
            ->pause(150)
            ->assertSeeIn('@output', '')
            ->waitForTextIn('@output', 'one', 1)

            // Page 2 — search is still "one"; type a new value
            ->waitForLivewire()->click('@page2')
            ->assertSeeIn('@page', '2')
            ->assertSeeIn('@output', 'one')
            ->type('@input', 'two')
            ->pause(150)
            ->assertSeeIn('@output', 'one')
            ->waitForTextIn('@output', 'two', 1)

            // Page 3
            ->waitForLivewire()->click('@page3')
            ->assertSeeIn('@page', '3')
            ->assertSeeIn('@output', 'two')
            ->type('@input', 'three')
            ->pause(150)
            ->assertSeeIn('@output', 'two')
            ->waitForTextIn('@output', 'three', 1)

            // Page 4
            ->waitForLivewire()->click('@page4')
            ->assertSeeIn('@page', '4')
            ->assertSeeIn('@output', 'three')
            ->type('@input', 'four')
            ->pause(150)
            ->assertSeeIn('@output', 'three')
            ->waitForTextIn('@output', 'four', 1)

            // Back → page 3 (history: true required)
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'back-three')
            ->pause(150)
            ->assertDontSeeIn('@output', 'back-three')
            ->waitForTextIn('@output', 'back-three', 1)

            // Back → page 2
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '2')
            ->type('@input', 'back-two')
            ->pause(150)
            ->assertDontSeeIn('@output', 'back-two')
            ->waitForTextIn('@output', 'back-two', 1)

            // Forward → page 3
            ->forward()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'fwd-three')
            ->pause(150)
            ->assertDontSeeIn('@output', 'fwd-three')
            ->waitForTextIn('@output', 'fwd-three', 1)

            // Forward → page 4
            ->forward()
            ->pause(100)
            ->assertSeeIn('@page', '4')
            ->type('@input', 'fwd-four')
            ->pause(150)
            ->assertDontSeeIn('@output', 'fwd-four')
            ->waitForTextIn('@output', 'fwd-four', 1)

            // Back → page 3 again
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'final')
            ->pause(150)
            ->assertDontSeeIn('@output', 'final')
            ->waitForTextIn('@output', 'final', 1)
        ;
    }

    public function test_debounce_attribute_works_on_form_object_property()
    {
        Livewire::visit(new class extends Component {
            public SearchForm $form;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="form.q" dusk="input" />
                    <span dusk="output">{{ $form->q }}</span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@output', '')
            ->pause(300)
            ->waitForTextIn('@output', 'livewire') // 5s clearly exceeds 2s debounce duration
        ;
    }

    public function test_blade_debounce_modifier_overrides_attribute_on_form_object()
    {
        Livewire::visit(new class extends Component {
            public SearchForm $form;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live.debounce.100ms="form.q" dusk="input" />
                    <span dusk="output">{{ $form->q }}</span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_debounce_attribute_does_not_delay_live_blur_network_update()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live.blur="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" dusk="blur-target">Blur</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@output', '')
            ->waitForLivewire()->click('@blur-target')
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_debounce_attribute_does_not_delay_blur_live_network_update()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.blur.live="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" dusk="blur-target">Blur</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@output', '')
            ->waitForLivewire()->click('@blur-target')
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_debounce_attribute_does_not_delay_live_enter_network_update()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live.enter="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" dusk="blur-target">Blur</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@output', '')
            // Blur alone must not commit (enter is the network trigger)
            ->click('@blur-target')
            ->pause(300)
            ->assertSeeIn('@output', '')
            ->click('@input')
            ->waitForLivewire()->keys('@input', '{enter}')
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_debounce_attribute_does_not_delay_lazy_network_update()
    {
        // .lazy (without .live) is treated as change + network; hasNetworkTriggers → no attribute debounce
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.lazy="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" dusk="blur-target">Blur</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->pause(300)
            ->assertSeeIn('@output', '')
            ->waitForLivewire()->click('@blur-target')
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_live_throttle_still_applies_debounce_attribute()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(500)]
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live.throttle.250ms="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            // 300ms between keystrokes, clearly exceeds the 250ms throttle window
            // but still below 500ms debounce duration
            ->typeSlowly('@input', 'ab', 300)
            ->assertSeeIn('@text', 'ab') // wire:text should update immediately
            ->pause(300) // Wait for the trailing request to be handled
            ->assertScript('window.requests', 1); // Requests collapse into one
    }

    public function test_debounces_requests_with_zero_duration()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(0)]
            public $foo;

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live="foo" dusk="input">
                    <span wire:text="foo" dusk="text"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->type('@input', 'a')
            ->assertSeeIn('@text', 'a') // wire:text should update immediately
            ->pause(50) // Wait for requests to be handled
            // Under default 150ms: a false fallback would still have requests === 0 here
            ->assertScript('window.requests', 1);
    }

    public function test_debounce_modifier_on_blur_live_uses_explicit_duration_not_attribute()
    {
        // .blur.live.debounce.Xms: blur is ephemeral; debounce is the network timing.
        // Attribute 2000ms must lose to Blade 100ms.
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.blur.live.debounce.100ms="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" dusk="blur-target">Blur</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@output', '')
            ->click('@blur-target')
            ->pause(50) // still debounced
            ->assertSeeIn('@output', '')
            ->pause(300) // already pass debounce duration
            ->assertSeeIn('@output', 'livewire')
        ;
    }

    public function test_debounce_attribute_works_on_nested_array_property()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(500)]
            public $filters = ['q' => ''];

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <input type="text" wire:model.live="filters.q" dusk="input" />
                    <span dusk="output" wire:text="filters.q"></span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'livewire', 50)
            ->assertSeeIn('@output', 'livewire') // wire:text should update immediately
            ->pause(300) // Wait for the request to be handled
            ->assertScript('window.requests', 1); // Only one request was sent
        ;
    }

    public function test_debounce_attribute_works_with_parent_expression()
    {
        Livewire::visit([
            new class extends Component {
                #[BaseDebounce(500)]
                public $search = '';

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <span dusk="output">{{ $search }}</span>
                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <input type="text" wire:model.live="$parent.search" dusk="input" />
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->typeSlowly('@input', 'from-child', 50)
            ->assertSeeIn('@output', '')
            ->waitForTextIn('@output', 'from-child', 1)
        ;
    }

    public function test_debounce_attribute_delays_method_action()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseDebounce(500)]
            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <button type="button" wire:click="increment" dusk="button">Increment</button>
                    <span dusk="output">{{ $count }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            // Rapid clicks should collapse into a single debounced request
            ->click('@button')
            ->click('@button')
            ->click('@button')
            ->assertSeeIn('@output', '0')
            ->pause(150)
            ->assertSeeIn('@output', '0')
            ->assertScript('window.requests', 0)
            ->waitForTextIn('@output', '1', 1)
            ->assertScript('window.requests', 1)
        ;
    }

    public function test_only_annotated_method_uses_attribute_debounce()
    {
        Livewire::visit(new class extends Component {
            public $slow = 0;
            public $fast = 0;

            #[BaseDebounce(500)]
            public function incrementSlow()
            {
                $this->slow++;
            }

            public function incrementFast()
            {
                $this->fast++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="incrementSlow" dusk="slow">Slow</button>
                    <button type="button" wire:click="incrementFast" dusk="fast">Fast</button>
                    <span dusk="slow-out">{{ $slow }}</span>
                    <span dusk="fast-out">{{ $fast }}</span>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@fast')
            ->assertSeeIn('@fast-out', '1')
            ->assertSeeIn('@slow-out', '0')
            ->click('@slow')
            ->assertSeeIn('@slow-out', '0')
            ->waitForTextIn('@slow-out', '1', 1)
        ;
    }

    public function test_debounce_attribute_on_method_allows_zero_duration()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseDebounce(0)]
            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <button type="button" wire:click="increment" dusk="button">Increment</button>
                    <span dusk="output">{{ $count }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@output', '1')
            ->assertScript('window.requests', 1)
        ;
    }

    public function test_debounce_attribute_works_on_method_with_parent_expression()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                #[BaseDebounce(500)]
                public function increment()
                {
                    $this->count++;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div x-init="window.requests = 0">
                        <span dusk="output">{{ $count }}</span>
                        <livewire:child />
                    </div>

                    @script
                    <script>
                        this.intercept(({ onSend }) => {
                            onSend(() => {
                                window.requests++
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" wire:click="$parent.increment" dusk="button">Increment</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->click('@button')
            ->click('@button')
            ->click('@button')
            ->assertSeeIn('@output', '0')
            ->pause(150)
            ->assertSeeIn('@output', '0')
            ->assertScript('window.requests', 0)
            ->waitForTextIn('@output', '1', 1)
            ->assertScript('window.requests', 1)
        ;
    }

    public function test_debounce_attribute_works_on_method_with_parameters()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseDebounce(500)]
            public function incrementBy($amount)
            {
                $this->count += $amount;
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <button type="button" wire:click="incrementBy(2)" dusk="button">Increment</button>
                    <span dusk="output">{{ $count }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@button')
            ->click('@button')
            ->click('@button')
            ->assertSeeIn('@output', '0')
            ->pause(150)
            ->assertSeeIn('@output', '0')
            ->assertScript('window.requests', 0)
            ->waitForTextIn('@output', '2', 1)
            ->assertScript('window.requests', 1)
        ;
    }

    public function test_debounce_attribute_delays_submit_action()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseDebounce(500)]
            public function save()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <form wire:submit="save">
                        <button type="submit" dusk="submit">Save</button>
                    </form>
                    <span dusk="output">{{ $count }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            // Rapid submits should collapse into a single debounced request
            ->click('@submit')
            ->click('@submit')
            ->click('@submit')
            ->assertSeeIn('@output', '0')
            ->pause(150)
            ->assertSeeIn('@output', '0')
            ->assertScript('window.requests', 0)
            ->waitForTextIn('@output', '1', 1)
            ->assertScript('window.requests', 1)
        ;
    }

    public function test_debounce_attribute_works_on_submit_with_parentheses_expression()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[BaseDebounce(500)]
            public function save()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div x-init="window.requests = 0">
                    <form wire:submit="save()">
                        <button type="submit" dusk="submit">Save</button>
                    </form>
                    <span dusk="output">{{ $count }}</span>
                </div>

                @script
                <script>
                    this.intercept(({ onSend }) => {
                        onSend(() => {
                            window.requests++
                        })
                    })
                </script>
                @endscript
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@submit')
            ->click('@submit')
            ->click('@submit')
            ->assertSeeIn('@output', '0')
            ->pause(150)
            ->assertSeeIn('@output', '0')
            ->assertScript('window.requests', 0)
            ->waitForTextIn('@output', '1', 1)
            ->assertScript('window.requests', 1)
        ;
    }
}

class SearchForm extends \Livewire\Form
{
    #[BaseDebounce(2000)]
    public string $q = '';
}