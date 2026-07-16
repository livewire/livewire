<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config()->set('livewire.update_engine', 'delta');
            config()->set('livewire.delta.store', 'file');
            config()->set('livewire.delta.minimum_html_bytes', 0);
            config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);
        };
    }

    public function test_global_delta_engine_updates_a_component_across_multiple_requests()
    {
        Livewire::visit(new class extends Component {
            public int $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="increment" wire:click="increment">Increment</button>
                    <span dusk="count">{{ $count }}</span>
                    <p style="display: none">{{ str_repeat('stable-content-', 1000) }}</p>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@count', '0')
            ->tap(fn ($browser) => $browser->script(<<<'JS'
                window.deltaRenderEffects = []

                Livewire.interceptMessage(({ onSuccess }) => {
                    onSuccess(({ payload }) => {
                        window.deltaRenderEffects.push(payload.effects)
                    })
                })
            JS))
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->assertScript("typeof window.deltaRenderEffects[0].html === 'string'", true)
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '2')
            ->assertScript("typeof window.deltaRenderEffects[1].htmlDelta === 'object'", true)
            ->assertScript("Array.isArray(window.deltaRenderEffects[1].htmlDelta.patches)", true)
            ->assertScript("typeof window.deltaRenderEffects[1].html === 'undefined'", true)
            ->tap(fn ($browser) => $browser->script(
                "Livewire.first().__instance.serverRenderedHtmlHash = 'tampered'"
            ))
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '3')
            ->assertScript("typeof window.deltaRenderEffects[2].html === 'string'", true)
        ;
    }

    public function test_multi_patch_delta_moves_a_kanban_card_between_columns()
    {
        Livewire::visit(new class extends Component {
            public int $revision = 0;

            public array $todo = ['Deploy application', 'Write documentation'];

            public array $done = ['Create project'];

            public function seedBaseline()
            {
                $this->revision++;
            }

            public function move()
            {
                $this->done[] = array_shift($this->todo);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="seed" wire:click="seedBaseline">Seed {{ $revision }}</button>
                    <button dusk="move" wire:click="move">Move</button>

                    <section dusk="todo">
                        @foreach ($todo as $card)
                            <article wire:key="todo-{{ $card }}">{{ $card }}</article>
                        @endforeach
                    </section>

                    <section style="display: none">
                        @foreach (range(1, 200) as $index)
                            <article>Unchanged middle card {{ $index }} in the doing column</article>
                        @endforeach
                    </section>

                    <section dusk="done">
                        @foreach ($done as $card)
                            <article wire:key="done-{{ $card }}">{{ $card }}</article>
                        @endforeach
                    </section>
                </div>
                HTML;
            }
        })
            ->tap(fn ($browser) => $browser->script(<<<'JS'
                window.kanbanDeltaEffects = []

                Livewire.interceptMessage(({ onSuccess }) => {
                    onSuccess(({ payload }) => {
                        window.kanbanDeltaEffects.push(payload.effects)
                    })
                })
            JS))
            ->waitForLivewire()->click('@seed')
            ->waitForLivewire()->click('@move')
            ->assertDontSeeIn('@todo', 'Deploy application')
            ->assertSeeIn('@done', 'Deploy application')
            ->assertScript("window.kanbanDeltaEffects[1].htmlDelta.patches.length >= 2", true)
            ->assertScript("typeof window.kanbanDeltaEffects[1].html === 'undefined'", true)
        ;
    }

    public function test_tampered_delta_is_rejected_before_morphing_and_triggers_a_full_resync()
    {
        Livewire::visit(new class extends Component {
            public int $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="increment" wire:click="increment">Increment</button>
                    <span dusk="count">{{ $count }}</span>
                    <p style="display: none">{{ str_repeat('stable-content-', 1000) }}</p>
                </div>
                HTML;
            }
        })
            ->tap(fn ($browser) => $browser->script(<<<'JS'
                window.deltaIntegrityEffects = []
                window.tamperNextDelta = false

                Livewire.interceptMessage(({ onSuccess }) => {
                    onSuccess(({ payload }) => {
                        window.deltaIntegrityEffects.push(payload.effects)

                        if (! window.tamperNextDelta || ! payload.effects.htmlDelta) return

                        payload.effects.htmlDelta.patches[0].insert = btoa('tampered')
                        window.tamperNextDelta = false
                    })
                })
            JS))
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->tap(fn ($browser) => $browser->script('window.tamperNextDelta = true'))
            ->waitForLivewire()->click('@increment')
            ->waitForTextIn('@count', '2')
            ->waitUntil('window.deltaIntegrityEffects.length >= 3')
            ->assertScript("typeof window.deltaIntegrityEffects[1].htmlDelta === 'object'", true)
            ->assertScript("typeof window.deltaIntegrityEffects[2].html === 'string'", true)
        ;
    }
}
