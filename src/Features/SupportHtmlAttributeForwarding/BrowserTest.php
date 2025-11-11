<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_html_attributes_are_forwarded_to_component()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1>Parent Component</h1>

                        <livewire:alert
                            type="error"
                            class="mb-4"
                            id="error-alert"
                            data-testid="my-alert"
                            dusk="alert-component"
                            wire:sort:item="1"
                        >
                            Something went wrong!
                        </livewire:alert>
                    </div>
                    HTML;
                }
            },
            'alert' => new class extends Component {
                public string $type = 'info';

                public function render()
                {
                    return <<<'HTML'
                    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
                        {{ $slot }}

                        <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@alert-component', 'Something went wrong!')
            ->assertAttribute('@alert-component', 'class', 'alert alert-error mb-4')
            ->assertAttribute('@alert-component', 'id', 'error-alert')
            ->assertAttribute('@alert-component', 'data-testid', 'my-alert')
            ->assertAttribute('@alert-component', 'wire:sort:item', '1')
            ->waitForLivewire()->click('@refresh')
            ->assertAttribute('@alert-component', 'class', 'alert alert-error mb-4')
            ->assertAttribute('@alert-component', 'id', 'error-alert')
            ->assertAttribute('@alert-component', 'data-testid', 'my-alert')
            ->assertAttribute('@alert-component', 'wire:sort:item', '1');
    }

    public function test_html_attributes_are_forwarded_to_a_lazy_component()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="parent-component">
                        <h1>Parent Component</h1>

                        <livewire:alert
                            type="error"
                            class="mb-4"
                            id="error-alert"
                            data-testid="my-alert"
                            dusk="alert-component"
                            wire:sort:item="1"
                            lazy
                        >
                            Something went wrong!
                        </livewire:alert>
                    </div>
                    HTML;
                }
            },
            'alert' => new class extends Component {
                public string $type = 'info';

                public function mount()
                {
                    usleep(200 * 1000); // 200ms
                }

                public function render()
                {
                    return <<<'HTML'
                    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
                        <h2>Alert Component</h2>

                        {{ $slot }}

                        <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertDontSee('Alert Component')
            ->assertAttribute('@parent-component > div', 'class', '')
            ->assertAttribute('@parent-component > div', 'id', '')
            ->assertAttributeMissing('@parent-component > div', 'data-testid')
            ->assertAttributeMissing('@parent-component > div', 'wire:sort:item')

            ->waitForText('Alert Component') // Wait for the lazy component to load
            ->assertAttribute('@parent-component > div', 'class', 'alert alert-error mb-4')
            ->assertAttribute('@parent-component > div', 'id', 'error-alert')
            ->assertAttribute('@parent-component > div', 'data-testid', 'my-alert')
            ->assertAttribute('@parent-component > div', 'wire:sort:item', '1')
            
            ->waitForLivewire()->click('@refresh')
            ->assertAttribute('@parent-component > div', 'class', 'alert alert-error mb-4')
            ->assertAttribute('@parent-component > div', 'id', 'error-alert')
            ->assertAttribute('@parent-component > div', 'data-testid', 'my-alert')
            ->assertAttribute('@parent-component > div', 'wire:sort:item', '1');
    }
}
