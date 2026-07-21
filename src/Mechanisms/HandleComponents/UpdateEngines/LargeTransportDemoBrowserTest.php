<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Component;
use Livewire\Livewire;

class LargeTransportDemoBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config()->set('livewire.update_engine', 'delta');
            config()->set('livewire.delta.store', 'file');
            config()->set('livewire.delta.cache_accelerator', false);
            config()->set('livewire.delta.minimum_html_bytes', 0);
            config()->set('livewire.delta.minimum_savings', 0.75);
            config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);
            config()->set('livewire.delta.compression_aware', false);
            config()->set('livewire.delta.request_compression', true);
            config()->set('livewire.delta.request_compression_minimum_bytes', 256);
            config()->set('livewire.delta.snapshot_references', true);
        };
    }

    public function test_large_transport_demo_exercises_each_kind_of_render_change()
    {
        Livewire::visit(new LargeTransportDemoComponent)
            ->waitForLivewireToLoad()
            ->assertSeeIn('@row-count', '400')
            ->assertSeeIn('@moved-row-position', '61')
            ->assertSeeIn('@transport-mode', 'initial')

            ->tap(fn ($browser) => $browser->script(<<<'JS'
                window.largeTransportDemo.requestEncodings = []
                window.largeTransportDemoOriginalFetch = window.fetch.bind(window)

                window.fetch = async (input, options = {}) => {
                    let headers = new Headers(options.headers || {})

                    window.largeTransportDemo.requestEncodings.push(
                        headers.get('Content-Encoding') || 'identity'
                    )

                    return await window.largeTransportDemoOriginalFetch(input, options)
                }
            JS))

            ->waitForLivewire()->click('@noop')
            ->assertScript('window.largeTransportDemo.responses', 1)
            ->assertScript("window.largeTransportDemo.history[0].mode", 'full')
            ->assertSeeIn('@transport-mode', 'full')
            ->assertScript("window.largeTransportDemo.history[0].selected > 0", true)
            ->assertScript("window.largeTransportDemo.history[0].saved", 0)

            ->waitForLivewire()->click('@noop')
            ->assertScript('window.largeTransportDemo.responses', 2)
            ->assertScript("window.largeTransportDemo.history[1].mode", 'same')
            ->assertSeeIn('@transport-mode', 'same')
            ->assertScript("window.largeTransportDemo.history[1].saved > 0", true)

            ->waitForLivewire()->click('@edit-middle')
            ->assertScript("window.largeTransportDemo.history[2].mode", 'fragments')
            ->assertSeeIn('@transport-mode', 'fragments')
            ->assertScript("Livewire.first().rows.find(row => row.id === 200).name", 'д字 東京 — ж世')
            ->assertSeeIn('@row-name-200', 'д字 東京 — ж世')
            ->assertScript("window.largeTransportDemo.history[2].saved > 0", true)

            ->waitForLivewire()->click('@move-block')
            ->assertScript("window.largeTransportDemo.history[3].mode", 'chunks')
            ->assertSeeIn('@transport-mode', 'chunks')
            ->assertSeeIn('@moved-row-position', '301')
            ->assertScript("window.largeTransportDemo.history[3].saved > 0", true)

            ->waitForLivewire()->click('@replace-all')
            ->assertScript("window.largeTransportDemo.history[4].mode", 'full')
            ->assertSeeIn('@transport-mode', 'full')
            ->assertSeeIn('@row-name-200', 'Replacement 200')
            ->assertSeeIn('@row-count', '400')
            ->assertScript('window.largeTransportDemo.responses', 5)
            ->assertScript("window.largeTransportDemo.history[4].saved", 0)
            ->assertScript("window.largeTransportDemo.history.every(item => Number.isInteger(item.full) && Number.isInteger(item.selected) && Number.isInteger(item.saved))", true)
            ->assertScript("window.largeTransportDemo.requestEncodings[0]", 'identity')
            ->assertScript("window.largeTransportDemo.requestEncodings[1]", 'gzip')
            ->assertScript("window.largeTransportDemo.requestEncodings.slice(1).every(encoding => encoding === 'gzip')", true)
        ;
    }
}

class LargeTransportDemoComponent extends Component
{
    public array $rows = [];

    public function mount(): void
    {
        foreach (range(1, 400) as $id) {
            $fingerprint = hash('sha256', "transport-demo-row-{$id}");

            $this->rows[] = [
                'id' => $id,
                'name' => "Customer {$id}",
                'status' => $id % 3 === 0 ? 'queued' : 'ready',
                'details' => "eu-central-{$id} / {$fingerprint} / {$fingerprint}",
            ];
        }
    }

    public function noop(): void
    {
        // Deliberately leave both the public state and HTML unchanged.
    }

    public function editMiddle(): void
    {
        $index = array_search(200, array_column($this->rows, 'id'), true);

        $this->rows[$index]['name'] = 'д字 東京 — ж世';
        $this->rows[$index]['status'] = 'unicode';
    }

    public function moveBlock(): void
    {
        $block = array_splice($this->rows, 60, 40);

        array_splice($this->rows, 300, 0, $block);
    }

    public function replaceAll(): void
    {
        foreach ($this->rows as &$row) {
            $fingerprint = hash('sha256', "replacement-row-{$row['id']}");

            $row['name'] = "Replacement {$row['id']}";
            $row['status'] = 'replaced';
            $row['details'] = "replacement / {$fingerprint} / {$fingerprint}";
        }

        unset($row);
    }

    public function render()
    {
        return <<<'HTML'
        <div
            x-data="{ transport: { mode: 'initial', full: 0, selected: 0, saved: 0, responses: 0, history: [], requestEncodings: [] } }"
            x-init="window.largeTransportDemo = transport"
        >
            @fragment('transport-dashboard')
                <section>
                    <button type="button" dusk="noop" wire:click="noop">No-op</button>
                    <button type="button" dusk="edit-middle" wire:click="editMiddle">Unicode middle edit</button>
                    <button type="button" dusk="move-block" wire:click="moveBlock">Move block</button>
                    <button type="button" dusk="replace-all" wire:click="replaceAll">Replace all</button>

                    <dl>
                        <div>
                            <dt>Rows</dt>
                            <dd dusk="row-count">{{ count($rows) }}</dd>
                        </div>
                        <div>
                            <dt>Moved row position</dt>
                            <dd dusk="moved-row-position">{{ array_search(61, array_column($rows, 'id'), true) + 1 }}</dd>
                        </div>
                        <div>
                            <dt>Transport mode</dt>
                            <dd dusk="transport-mode" x-text="transport.mode">initial</dd>
                        </div>
                        <div>
                            <dt>Full response bytes</dt>
                            <dd dusk="transport-full-bytes" x-text="transport.full">0</dd>
                        </div>
                        <div>
                            <dt>Selected response bytes</dt>
                            <dd dusk="transport-selected-bytes" x-text="transport.selected">0</dd>
                        </div>
                        <div>
                            <dt>Saved bytes</dt>
                            <dd dusk="transport-saved-bytes" x-text="transport.saved">0</dd>
                        </div>
                    </dl>
                </section>
            @endfragment

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Stable payload</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @fragment('row-'.$row['id'])
                            <tr wire:key="transport-row-{{ $row['id'] }}">
                                <td>{{ $row['id'] }}</td>
                                <td @if ($row['id'] === 200) dusk="row-name-200" @endif>{{ $row['name'] }}</td>
                                <td>{{ $row['status'] }}</td>
                                <td>{{ $row['details'] }}</td>
                            </tr>
                        @endfragment
                    @endforeach
                </tbody>
            </table>
        </div>

        @script
        <script>
            this.interceptMessage(({ onSuccess }) => {
                onSuccess(({ payload }) => {
                    let effects = payload.effects
                    let render = effects.render
                    let stats = window.largeTransportDemo

                    if (! stats) return

                    let item

                    if (render?.stats) {
                        item = {
                            mode: render.mode,
                            full: render.stats.full,
                            selected: render.stats.selected,
                            saved: render.stats.saved,
                        }
                    } else if (typeof effects.html === 'string') {
                        let bytes = new TextEncoder().encode(JSON.stringify(effects)).byteLength

                        item = {
                            mode: 'full',
                            full: bytes,
                            selected: bytes,
                            saved: 0,
                        }
                    } else {
                        return
                    }

                    stats.mode = item.mode
                    stats.full = item.full
                    stats.selected = item.selected
                    stats.saved = item.saved
                    stats.responses++
                    stats.history.push(item)
                })
            })
        </script>
        @endscript
        HTML;
    }
}
