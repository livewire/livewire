<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends TestCase
{
    public function test_html_attributes_are_forwarded_to_component()
    {
        Livewire::test([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <!-- ... -->

                        <livewire:alert
                            type="error"
                            class="mb-4"
                            id="error-alert"
                            data-testid="my-alert"
                        />
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
                        <!-- ... -->
                    </div>
                    HTML;
                }
            }
        ])
        ->assertDontSeeHtml('type="error"')
        ->assertSeeHtml('class="alert alert-error mb-4"')
        ->assertSeeHtml('id="error-alert"')
        ->assertSeeHtml('data-testid="my-alert"')
        ;
    }
}
