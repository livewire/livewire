<?php

namespace Livewire\Features\SupportMorphing;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function is_does_not_rerender_alpine_components_removed_from_the_dom()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $foo = true;

            public $bar = [
                'baz' => [
                    'isVisible' => true,
                ],
            ];

            public function hide()
            {
                $this->foo = false;
                $this->bar = [];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div>
                        @if ($foo)
                            <div x-data="{ state: $wire.entangle('bar.baz') }">
                                <template x-if="state.isVisible">
                                    <div dusk="output">
                                        Foo
                                    </div>
                                </template>
                            </div>
                        @endif
                    </div>

                    <button dusk="hideButton" wire:click="hide" type="button">Hide</button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@output', 'Foo')
            ->click('@hideButton')
            ->waitUntilMissing('@output')
            ->assertConsoleLogEmpty()
        ;
    }

    /** @test */
    public function conditional_watchers_are_removed_in_time_so_they_dont_trigger_with_stale_component_state()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $foo = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    @if ($foo)
                        <div x-data="{ bar: @entangle('foo') }" x-init="$watch('bar', value => {
                            if (! value) throw 'some error'
                        })" dusk="output">
                            foo
                        </div>
                    @endif

                    <button dusk="button" wire:click="$set('foo', false)" type="button">Hide</button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@output', 'foo')
            ->click('@button')
            ->waitUntilMissing('@output')
            ->assertConsoleLogEmpty()
        ;
    }

    /** @test */
    public function can_access_latest_dom_when_conditional_scripts_are_run()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    @if ($show)
                        <div dusk="output">
                            nested script
                        </div>

                        @script
                        <script>
                            if (! $wire.$el.querySelector('[dusk="output"]')) {
                                throw 'cannot find element'
                            }
                        </script>
                        @endscript
                    @endif

                    <button dusk="button" wire:click="$set('show', true)" type="button">Show</button>
                </div>
                HTML;
            }
        })
            ->click('@button')
            ->waitForText('nested script')
            ->assertConsoleLogEmpty()
        ;
    }

    /** @test */
    public function can_reference_script_alpine_components_from_new_dom()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    @if ($show)
                        <div dusk="output">
                            nested script
                        </div>

                        <div x-data="test">
                            <h1 x-text="return testing.value"></h1>
                        </div>

                        @script
                        <script>
                            Alpine.data('test', (el) => {
                                return { testing: { value: 'worked' } }
                            })
                        </script>
                        @endscript
                    @endif

                    <button dusk="button" wire:click="$set('show', true)" type="button">Show</button>
                </div>
                HTML;
            }
        })
            ->click('@button')
            ->waitForText('nested script')
            ->assertConsoleLogEmpty()
        ;
    }
}
