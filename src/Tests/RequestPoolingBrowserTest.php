<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class RequestPoolingBrowserTest extends \Tests\BrowserTestCase
{
    public function test_component_not_found_error_is_not_thrown_when_two_requests_are_sent_in_a_row()
    {
        Livewire::visit([
            new class () extends Component {
                public int $selectedId = 0;

                public function select(int $id): void
                {
                    $this->selectedId = $id;

                    usleep(500*1000); // 500ms
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button wire:click="select(1)" class="size-10" dusk="select-1">1</button>

                        <div>
                            @if ($this->selectedId !== 0)
                                <livewire:child :key="'key'.$this->selectedId" :item="$this->selectedId" />
                            @endif
                        </div>

                        {{-- This needs to be here... --}}
                        <div x-data="{ show: false }" x-cloak>
                            <div x-show="show"></div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public $item;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        Child {{ $item }}
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->click('@select-1')
            // Pause a moment before clicking again to ensure the call is in a new request...
            ->pause(50)
            ->click('@select-1')
            // Wait for the second request to complete...
            ->pause(600)
            ->assertConsoleLogHasNoErrors();
    }
}
