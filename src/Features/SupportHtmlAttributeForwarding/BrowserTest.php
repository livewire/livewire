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
            ->waitForLivewire()->click('@refresh')
            ->assertAttribute('@alert-component', 'class', 'alert alert-error mb-4')
            ->assertAttribute('@alert-component', 'id', 'error-alert')
            ->assertAttribute('@alert-component', 'data-testid', 'my-alert');
    }
}
