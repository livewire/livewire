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
            config()->set('livewire.delta.compression_aware', false);
        };
    }

    public function test_global_delta_engine_materializes_full_and_cached_splice_responses()
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
            ->assertScript("window.deltaRenderEffects[0].render.mode", 'full')
            ->assertScript("typeof window.deltaRenderEffects[0].html === 'string'", true)
            ->assertScript("typeof window.deltaRenderEffects[0].htmlHash === 'undefined'", true)
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '2')
            ->assertScript("window.deltaRenderEffects[1].render.mode", 'splice')
            ->assertScript("Array.isArray(window.deltaRenderEffects[1].render.patches)", true)
            ->assertScript("typeof window.deltaRenderEffects[1].html === 'string'", true)
            ->assertScript("window.deltaRenderEffects[1].render.stats.saved > 0", true)
            ->tap(fn ($browser) => $browser->script(
                'Livewire.first().__instance.forgetServerRenderedHtml()'
            ))
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '3')
            ->assertScript("window.deltaRenderEffects[2].render.mode", 'full')
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
            ->assertScript("window.kanbanDeltaEffects[0].render.mode", 'full')
            ->assertScript("typeof window.kanbanDeltaEffects[0].html === 'string'", true)
            ->assertScript("window.kanbanDeltaEffects[1].render.mode", 'splice')
            ->assertScript("window.kanbanDeltaEffects[1].render.patches.length >= 2", true)
            ->assertScript("typeof window.kanbanDeltaEffects[1].html === 'string'", true)
        ;
    }

    public function test_corrupted_splice_triggers_full_resync_without_replaying_the_action()
    {
        Livewire::visit(new class extends Component {
            public int $count = 0;

            public int $incrementCalls = 0;

            public function increment()
            {
                $this->count++;
                $this->incrementCalls++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="increment" wire:click="increment">Increment</button>
                    <span dusk="count">{{ $count }}</span>
                    <span dusk="increment-calls">{{ $incrementCalls }}</span>
                    <p style="display: none">{{ str_repeat('stable-content-', 1000) }}</p>
                </div>
                HTML;
            }
        })
            ->tap(fn ($browser) => $browser->script(<<<'JS'
                window.deltaIntegrityEffects = []
                window.tamperNextSplice = false
                window.livewireOriginalFetch = window.fetch.bind(window)

                window.fetch = async (input, options = {}) => {
                    let response = await window.livewireOriginalFetch(input, options)

                    if (! window.tamperNextSplice) return response

                    let json

                    try {
                        json = await response.clone().json()
                    } catch (error) {
                        return response
                    }

                    let component = json.components?.[0]
                    let render = component?.effects?.render

                    if (render?.mode !== 'splice' || ! render.patches?.length) return response

                    render.patches[0].insert = btoa('corrupted-render')
                    window.tamperNextSplice = false

                    let headers = new Headers(response.headers)
                    headers.delete('content-length')
                    headers.delete('content-encoding')

                    return new Response(JSON.stringify(json), {
                        status: response.status,
                        statusText: response.statusText,
                        headers,
                    })
                }

                Livewire.interceptMessage(({ onSuccess }) => {
                    onSuccess(({ payload }) => {
                        window.deltaIntegrityEffects.push({
                            mode: payload.effects.renderRecovery
                                ? 'recovery'
                                : payload.effects.render?.mode
                                    || (typeof payload.effects.html === 'string' ? 'full' : null),
                            renderMode: payload.effects.render?.mode,
                            hasHtml: typeof payload.effects.html === 'string',
                        })
                    })
                })
            JS))
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1')
            ->assertSeeIn('@increment-calls', '1')
            ->assertScript("window.deltaIntegrityEffects[0].mode", 'full')
            ->tap(fn ($browser) => $browser->script('window.tamperNextSplice = true'))
            ->waitForLivewire()->click('@increment')
            ->waitForTextIn('@count', '2')
            ->assertSeeIn('@increment-calls', '2')
            ->waitUntil('window.deltaIntegrityEffects.length >= 3')
            ->assertScript("window.deltaIntegrityEffects[1].mode", 'recovery')
            ->assertScript("window.deltaIntegrityEffects[1].renderMode", 'splice')
            ->assertScript("window.deltaIntegrityEffects[1].hasHtml", false)
            ->assertScript("window.deltaIntegrityEffects[2].mode", 'full')
            ->assertScript("window.deltaIntegrityEffects[2].hasHtml", true)
            ->assertScript("Livewire.first().count", 2)
            ->assertScript("Livewire.first().incrementCalls", 2)
        ;
    }
}
