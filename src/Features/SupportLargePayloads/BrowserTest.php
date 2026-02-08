<?php

namespace Livewire\Features\SupportLargePayloads;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /**
     * Test that large payloads (300KB+) can be sent without causing
     * "Maximum call stack size exceeded" errors.
     *
     * This regression test ensures the fix for the fingerprint getter
     * in js/request/action.js continues to work. The original bug used
     * String.fromCharCode(...array) which failed with arrays > ~125k elements.
     */
    public function test_can_send_large_payload_without_stack_overflow()
    {
        Livewire::visit(
            new class extends Component {
                public $receivedLength = 0;

                public function saveData($data)
                {
                    $this->receivedLength = strlen($data);
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button dusk="send">Send Large Payload</button>
                        <span dusk="result">{{ $receivedLength }}</span>
                    </div>

                    @script
                    <script>
                        document.querySelector('[dusk="send"]').addEventListener('click', () => {
                            // Generate a 300KB payload (similar to a base64-encoded image)
                            // This size previously caused stack overflow in the fingerprint getter
                            let largePayload = 'x'.repeat(300 * 1024);

                            $wire.call('saveData', largePayload);
                        });
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()
            ->assertSeeIn('@result', '0')
            ->click('@send')
            ->waitForTextIn('@result', '307200')
            ->assertSeeIn('@result', '307200')
            ->assertConsoleLogHasNoErrors();
    }
}
