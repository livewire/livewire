<?php

namespace Livewire\Features\SupportDisablingFormsDuringRequest;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_range_input_is_disabled_not_readonly_during_form_submission()
    {
        Livewire::visit(new class extends Component {
            public function submit() {
                usleep(500 * 1000);
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <form wire:submit="submit">
                        <input type="range" dusk="range" />
                        <input type="text" dusk="text" />
                        <button type="submit" dusk="submit">Submit</button>
                    </form>
                </div>
                HTML;
            }
        })
        ->click('@submit')
        ->pause(10)
        // During request: range should be disabled (not readonly)
        ->assertAttribute('@range', 'disabled', 'true')
        ->assertAttributeMissing('@range', 'readonly')
        // During request: text should be readonly (not disabled)
        ->assertAttribute('@text', 'readonly', 'true')
        ->assertAttributeMissing('@text', 'disabled')
        // Wait for the request to finish
        ->pause(600)
        // After request: attributes should be cleared
        ->assertAttributeMissing('@range', 'disabled')
        ->assertAttributeMissing('@text', 'readonly');
    }

    public function test_color_input_is_disabled_not_readonly_during_form_submission()
    {
        Livewire::visit(new class extends Component {
            public function submit() {
                usleep(500 * 1000);
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <form wire:submit="submit">
                        <input type="color" dusk="color" />
                        <button type="submit" dusk="submit">Submit</button>
                    </form>
                </div>
                HTML;
            }
        })
        ->click('@submit')
        ->pause(10)
        ->assertAttribute('@color', 'disabled', 'true')
        ->assertAttributeMissing('@color', 'readonly')
        ->pause(600)
        ->assertAttributeMissing('@color', 'disabled');
    }
}
