<?php

namespace Explorations;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class MergeSnapshotBrowserTest extends BrowserTestCase
{
    /**
     * Scenario 1: Server adds item to nested array
     * Question: What gets replaced? Just the new item? The array? The whole config?
     */
    function test_scenario_1_server_adds_item_to_nested_array()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'My Chart',
                'series' => [
                    ['name' => 'Series A', 'data' => [1, 2, 3]],
                    ['name' => 'Series B', 'data' => [4, 5, 6]],
                ]
            ];

            public function addSeries()
            {
                $this->config['series'][] = ['name' => 'Series C', 'data' => [7, 8, 9]];
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="add" wire:click="addSeries">Add Series</button>
                        <div dusk="count">Series count: {{ count($config['series']) }}</div>
                        <div dusk="debug" x-data x-text="JSON.stringify(window.__mergeSnapshotDebug || [])"></div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@count', 'Series count: 2')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@count', 'Series count: 3')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 1: Server adds item to nested array', $debug[0] ?? []);
            });
    }

    /**
     * Scenario 2: Server modifies deep nested property
     * Question: How granular is the diff? How granular is the replacement?
     */
    function test_scenario_2_server_modifies_deep_nested_property()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'My Chart',
                'series' => [
                    ['name' => 'Series A', 'data' => [1, 2, 3]],
                    ['name' => 'Series B', 'data' => [4, 5, 6]],
                ]
            ];

            public function changeSeriesName()
            {
                $this->config['series'][0]['name'] = 'CHANGED';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="change" wire:click="changeSeriesName">Change Name</button>
                        <div dusk="name">Name: {{ $config['series'][0]['name'] }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@name', 'Name: Series A')
            ->waitForLivewire()->click('@change')
            ->assertSeeIn('@name', 'Name: CHANGED')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 2: Server modifies deep nested property', $debug[0] ?? []);
            });
    }

    /**
     * Scenario 3: Client has ephemeral changes, server changes different property
     * Question: Does client's ephemeral change get blown away?
     */
    function test_scenario_3_client_ephemeral_vs_server_change()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original Title',
                'subtitle' => 'Original Subtitle',
                'series' => [
                    ['name' => 'Series A'],
                ]
            ];

            public function changeSeriesOnServer()
            {
                // Server changes series, but NOT title
                $this->config['series'][0]['name'] = 'SERVER CHANGED';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div x-data>
                        <input dusk="title-input" type="text" wire:model="config.title" />
                        <button dusk="server-change" wire:click="changeSeriesOnServer">Server Change Series</button>

                        <div dusk="title-ephemeral" x-text="$wire.config.title"></div>
                        <div dusk="title-server">{{ $config['title'] }}</div>
                        <div dusk="series-name">{{ $config['series'][0]['name'] }}</div>

                        <button dusk="check-ephemeral" @click="
                            window.__ephemeralCheck = {
                                title: $wire.config.title,
                                series: JSON.parse(JSON.stringify($wire.config.series))
                            }
                        ">Check Ephemeral</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@title-ephemeral', 'Original Title')
            ->assertSeeIn('@series-name', 'Series A')
            // Client changes title (ephemeral only, no network yet)
            ->type('@title-input', 'CLIENT CHANGED TITLE')
            ->pause(100)
            ->assertSeeIn('@title-ephemeral', 'CLIENT CHANGED TITLE')
            ->assertSeeIn('@title-server', 'Original Title') // Server hasn't seen it
            // Now server changes series (which should NOT touch title)
            ->waitForLivewire()->click('@server-change')
            ->pause(100)
            ->assertSeeIn('@series-name', 'SERVER CHANGED')
            // THE KEY QUESTION: Is our client-side title change still there?
            ->click('@check-ephemeral')
            ->pause(50)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $ephemeralCheck = $browser->script('return window.__ephemeralCheck');
                $this->logDebug('SCENARIO 3: Client ephemeral vs server change', $debug[0] ?? [], [
                    'ephemeralCheck' => $ephemeralCheck[0] ?? null,
                    'question' => 'Was CLIENT CHANGED TITLE preserved?'
                ]);
            });
    }

    /**
     * Scenario 4: Client makes change to same property server changes
     * Question: What happens with conflicting changes?
     */
    function test_scenario_4_conflicting_changes()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original',
            ];

            public function serverChangeTitle()
            {
                $this->config['title'] = 'SERVER VALUE';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div x-data>
                        <input dusk="input" type="text" wire:model="config.title" />
                        <button dusk="server" wire:click="serverChangeTitle">Server Change</button>
                        <div dusk="ephemeral" x-text="$wire.config.title"></div>
                        <div dusk="server-val">{{ $config['title'] }}</div>
                    </div>
                BLADE;
            }
        })
            ->type('@input', 'CLIENT VALUE')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'CLIENT VALUE')
            ->waitForLivewire()->click('@server')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 4: Conflicting changes (same property)', $debug[0] ?? []);
            });
    }

    /**
     * Scenario 5: Wire:model.live sends update, server makes additional change
     * This tests the "updates" parameter being applied to oldCanonical
     */
    function test_scenario_5_wire_model_live_with_server_side_effect()
    {
        Livewire::visit(new class extends Component {
            public $form = [
                'name' => '',
                'slug' => '',
            ];

            public function updatedFormName($value)
            {
                // Server auto-generates slug from name
                $this->form['slug'] = strtolower(str_replace(' ', '-', $value));
            }

            public function render()
            {
                return <<<'BLADE'
                    <div x-data>
                        <input dusk="name" type="text" wire:model.live="form.name" />
                        <div dusk="slug">Slug: {{ $form['slug'] }}</div>
                        <div dusk="ephemeral-slug" x-text="'Ephemeral slug: ' + $wire.form.slug"></div>
                    </div>
                BLADE;
            }
        })
            ->type('@name', 'Hello World')
            ->waitForTextIn('@slug', 'hello-world')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 5: wire:model.live with server side effect', $debug ?? []);
            });
    }

    /**
     * Scenario 6: Server removes item from array
     * Question: How is removal handled in the diff?
     */
    function test_scenario_6_server_removes_item_from_array()
    {
        Livewire::visit(new class extends Component {
            public $items = ['a', 'b', 'c', 'd'];

            public function removeItem()
            {
                array_pop($this->items);
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="remove" wire:click="removeItem">Remove</button>
                        <div dusk="count">Count: {{ count($items) }}</div>
                        <div dusk="items">{{ implode(',', $items) }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', 'a,b,c,d')
            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@items', 'a,b,c')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 6: Server removes item from array', $debug[0] ?? []);
            });
    }

    /**
     * Scenario 7: Multiple root properties, only one changes
     * Question: Does changing one root property affect others?
     */
    function test_scenario_7_multiple_root_properties()
    {
        Livewire::visit(new class extends Component {
            public $propA = ['nested' => 'value A'];
            public $propB = ['nested' => 'value B'];
            public $propC = ['nested' => 'value C'];

            public function changePropB()
            {
                $this->propB['nested'] = 'CHANGED B';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div x-data>
                        <button dusk="change" wire:click="changePropB">Change B</button>
                        <div dusk="a">A: {{ $propA['nested'] }}</div>
                        <div dusk="b">B: {{ $propB['nested'] }}</div>
                        <div dusk="c">C: {{ $propC['nested'] }}</div>

                        <button dusk="mutate-a" @click="$wire.propA.nested = 'CLIENT A'">Mutate A Client</button>
                        <div dusk="a-ephemeral" x-text="$wire.propA.nested"></div>
                    </div>
                BLADE;
            }
        })
            // Client mutates propA
            ->click('@mutate-a')
            ->pause(50)
            ->assertSeeIn('@a-ephemeral', 'CLIENT A')
            // Server changes propB
            ->waitForLivewire()->click('@change')
            ->pause(100)
            ->assertSeeIn('@b', 'B: CHANGED B')
            // Check if propA client mutation survived
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $aEphemeral = $browser->text('@a-ephemeral');
                $this->logDebug('SCENARIO 7: Multiple root properties', $debug[0] ?? [], [
                    'propA_ephemeral_after_propB_change' => $aEphemeral,
                    'question' => 'Was CLIENT A preserved when server changed propB?'
                ]);
            });
    }

    /**
     * Scenario 8: Nested object within array item changes
     */
    function test_scenario_8_deeply_nested_change()
    {
        Livewire::visit(new class extends Component {
            public $data = [
                'charts' => [
                    [
                        'id' => 1,
                        'config' => [
                            'series' => [
                                ['name' => 'A', 'values' => [1, 2, 3]]
                            ]
                        ]
                    ]
                ]
            ];

            public function changeDeepValue()
            {
                $this->data['charts'][0]['config']['series'][0]['values'][1] = 999;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="change" wire:click="changeDeepValue">Change Deep Value</button>
                        <div dusk="value">Value: {{ $data['charts'][0]['config']['series'][0]['values'][1] }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@value', 'Value: 2')
            ->waitForLivewire()->click('@change')
            ->assertSeeIn('@value', 'Value: 999')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $this->logDebug('SCENARIO 8: Deeply nested change', $debug[0] ?? []);
            });
    }

    private function logDebug($scenario, $debug, $extra = [])
    {
        $logFile = __DIR__ . '/2026-02-02-merge-snapshot-investigation.md';

        $content = "\n\n---\n\n## $scenario\n\n";
        $content .= "**Timestamp:** " . date('Y-m-d H:i:s') . "\n\n";

        if (!empty($debug)) {
            if (is_array($debug) && isset($debug[0]) && is_array($debug[0])) {
                // Multiple debug entries
                foreach ($debug as $i => $entry) {
                    $content .= "### Request " . ($i + 1) . "\n\n";
                    $content .= $this->formatDebugEntry($entry);
                }
            } else {
                $content .= $this->formatDebugEntry($debug);
            }
        } else {
            $content .= "**No debug data captured**\n\n";
        }

        if (!empty($extra)) {
            $content .= "### Extra Observations\n\n";
            $content .= "```json\n" . json_encode($extra, JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        file_put_contents($logFile, $content, FILE_APPEND);
    }

    private function formatDebugEntry($entry)
    {
        $content = "";

        if (isset($entry['dirty'])) {
            $content .= "### Dirty Keys (what diff() found changed)\n\n";
            $content .= "```json\n" . json_encode($entry['dirty'], JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        if (isset($entry['replacements'])) {
            $content .= "### Replacements (what actually got replaced)\n\n";
            foreach ($entry['replacements'] as $r) {
                $content .= "- **Dirty key:** `{$r['dirtyKey']}` → **Root key:** `{$r['rootKey']}`\n";
            }
            $content .= "\n";
        }

        if (isset($entry['updates']) && !empty($entry['updates'])) {
            $content .= "### Updates (sent to server)\n\n";
            $content .= "```json\n" . json_encode($entry['updates'], JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        if (isset($entry['ephemeralBefore']) && isset($entry['ephemeralAfter'])) {
            $content .= "### Ephemeral State\n\n";
            $content .= "**Before:**\n```json\n" . json_encode($entry['ephemeralBefore'], JSON_PRETTY_PRINT) . "\n```\n\n";
            $content .= "**After:**\n```json\n" . json_encode($entry['ephemeralAfter'], JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        return $content;
    }
}
