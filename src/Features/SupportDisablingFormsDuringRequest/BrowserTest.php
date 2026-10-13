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

    public function test_form_lock_does_not_re_enable_elements_the_server_rendered_as_disabled_or_readonly()
    {
        Livewire::visit(new class extends Component {
            public bool $locked = false;

            public function submit()
            {
                $this->locked = true;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <form wire:submit="submit">
                        <input type="text" dusk="text" @readonly($locked) />
                        <input type="checkbox" dusk="checkbox" @disabled($locked) />
                        <select dusk="select" @disabled($locked)><option>x</option></select>
                        <button type="submit" dusk="button" @disabled($locked)>Send</button>
                    </form>

                    <p dusk="state">server locked = {{ $locked ? 'true' : 'false' }}</p>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@state', 'server locked = false')
            ->assertAttributeMissing('@text', 'readonly')
            ->assertAttributeMissing('@checkbox', 'disabled')
            ->assertAttributeMissing('@select', 'disabled')
            ->assertAttributeMissing('@button', 'disabled')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@state', 'server locked = true')
            // After the request, server-rendered locked state must remain
            ->assertAttribute('@text', 'readonly', 'true')
            ->assertAttribute('@checkbox', 'disabled', 'true')
            ->assertAttribute('@select', 'disabled', 'true')
            ->assertAttribute('@button', 'disabled', 'true');
    }
}
