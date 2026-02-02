<?php

namespace Livewire\Features\SupportMergeSnapshotInvestigation;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
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
     * THIS IS THE KEY TEST - does client's ephemeral change survive?
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
                    <div>
                        <input dusk="title-input" type="text" wire:model="config.title" />
                        <button dusk="server-change" wire:click="changeSeriesOnServer">Server Change Series</button>

                        <div dusk="title-ephemeral" x-text="$wire.config.title"></div>
                        <div dusk="title-server">{{ $config['title'] }}</div>
                        <div dusk="series-name">{{ $config['series'][0]['name'] }}</div>
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
            ->assertSeeIn('@title-server', 'Original Title')
            // Server changes series (should NOT touch title)
            ->waitForLivewire()->click('@server-change')
            ->pause(100)
            ->assertSeeIn('@series-name', 'SERVER CHANGED')
            // THE KEY QUESTION: Is our client-side title change still there?
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $titleEphemeral = $browser->text('@title-ephemeral');
                $this->logDebug('SCENARIO 3: Client ephemeral vs server change', $debug[0] ?? [], [
                    'title_ephemeral_after_server_change' => $titleEphemeral,
                    'question' => 'Was CLIENT CHANGED TITLE preserved? Answer: ' . ($titleEphemeral === 'CLIENT CHANGED TITLE' ? 'YES' : 'NO - it was: ' . $titleEphemeral)
                ]);
            });
    }

    /**
     * Scenario 4: Conflicting changes - client and server change same property
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
                    <div>
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
                $ephemeral = $browser->text('@ephemeral');
                $server = $browser->text('@server-val');
                $this->logDebug('SCENARIO 4: Conflicting changes', $debug[0] ?? [], [
                    'ephemeral_value' => $ephemeral,
                    'server_rendered_value' => $server,
                    'analysis' => 'Client had CLIENT VALUE, server set SERVER VALUE. What won?'
                ]);
            });
    }

    /**
     * Scenario 5: wire:model.live - tests the "updates" parameter
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
                $this->form['slug'] = strtolower(str_replace(' ', '-', $value));
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="name" type="text" wire:model.live="form.name" />
                        <div dusk="slug">Slug: {{ $form['slug'] }}</div>
                        <div dusk="ephemeral-slug" x-text="'Ephemeral: ' + $wire.form.slug"></div>
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
     * Scenario 7: Multiple root properties - changing one shouldn't affect others
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
                    <div>
                        <button dusk="change" wire:click="changePropB">Change B</button>
                        <div dusk="a">A: {{ $propA['nested'] }}</div>
                        <div dusk="b">B: {{ $propB['nested'] }}</div>
                        <div dusk="c">C: {{ $propC['nested'] }}</div>

                        <button dusk="mutate-a" x-on:click="$wire.propA.nested = 'CLIENT A'">Mutate A Client</button>
                        <div dusk="a-ephemeral" x-text="$wire.propA.nested"></div>
                    </div>
                BLADE;
            }
        })
            // Client mutates propA (ephemeral)
            ->click('@mutate-a')
            ->pause(50)
            ->assertSeeIn('@a-ephemeral', 'CLIENT A')
            // Server changes propB (different root property)
            ->waitForLivewire()->click('@change')
            ->pause(100)
            ->assertSeeIn('@b', 'B: CHANGED B')
            // Check if propA client mutation survived
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $aEphemeral = $browser->text('@a-ephemeral');
                $this->logDebug('SCENARIO 7: Multiple root properties', $debug[0] ?? [], [
                    'propA_ephemeral_after_propB_change' => $aEphemeral,
                    'question' => 'Was CLIENT A preserved when server changed propB? Answer: ' . ($aEphemeral === 'CLIENT A' ? 'YES' : 'NO')
                ]);
            });
    }

    /**
     * Scenario 8: Very deeply nested change
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

    /**
     * Scenario 9: Client makes change DURING a pending request (SAME root property)
     * With surgical updates, changes made during a request should be preserved.
     */
    function test_scenario_9_change_during_pending_request_same_root()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original',
                'count' => 0,
            ];

            public function slowIncrement()
            {
                usleep(500000); // 500ms delay
                $this->config['count']++;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title-input" type="text" wire:model="config.title" />
                        <button dusk="slow" wire:click="slowIncrement">Slow Increment</button>
                        <div dusk="count">Count: {{ $config['count'] }}</div>
                        <div dusk="title-ephemeral" x-text="$wire.config.title"></div>
                        <div dusk="title-server">{{ $config['title'] }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@count', 'Count: 0')
            // Start slow request (don't wait for it)
            ->click('@slow')
            // Immediately type while request is pending
            ->pause(100) // Small pause to ensure request started
            ->type('@title-input', 'TYPED DURING REQUEST')
            ->pause(100)
            // Check ephemeral state while request pending
            ->assertSeeIn('@title-ephemeral', 'TYPED DURING REQUEST')
            // Wait for slow request to complete
            ->waitForTextIn('@count', 'Count: 1')
            ->pause(100)
            // With surgical updates, our typing should be preserved!
            ->assertSeeIn('@title-ephemeral', 'TYPED DURING REQUEST')
        ;
    }

    /**
     * Scenario 10: Client makes change DURING a pending request (DIFFERENT root property)
     * When changes are to a DIFFERENT root property, they should be preserved.
     */
    function test_scenario_10_change_during_pending_request_different_root()
    {
        Livewire::visit(new class extends Component {
            public $counter = 0;
            public $title = 'Original';

            public function slowIncrement()
            {
                usleep(500000); // 500ms delay
                $this->counter++;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title-input" type="text" wire:model="title" />
                        <button dusk="slow" wire:click="slowIncrement">Slow Increment</button>
                        <div dusk="counter">Counter: {{ $counter }}</div>
                        <div dusk="title-ephemeral" x-text="$wire.title"></div>
                        <div dusk="title-server">{{ $title }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@counter', 'Counter: 0')
            // Start slow request
            ->click('@slow')
            // Type while request is pending (different root property)
            ->pause(100)
            ->type('@title-input', 'TYPED DURING REQUEST')
            ->pause(100)
            ->assertSeeIn('@title-ephemeral', 'TYPED DURING REQUEST')
            // Wait for slow request to complete
            ->waitForTextIn('@counter', 'Counter: 1')
            ->pause(100)
            ->tap(function ($browser) {
                $debug = $browser->script('return window.__mergeSnapshotDebug');
                $titleEphemeral = $browser->text('@title-ephemeral');
                $titleServer = $browser->text('@title-server');
                $this->logDebug('SCENARIO 10: Change during request (DIFFERENT root)', $debug[0] ?? [], [
                    'title_ephemeral_after_response' => $titleEphemeral,
                    'title_server_rendered' => $titleServer,
                    'question' => 'Was TYPED DURING REQUEST preserved (different root)? Answer: ' .
                        ($titleEphemeral === 'TYPED DURING REQUEST' ? 'YES' : 'NO - it was: ' . $titleEphemeral)
                ]);
            });
    }

    /**
     * Scenario 11: Nested array removals at different levels
     * Verifies the reverse-order sort works for complex nested structures
     */
    function test_scenario_11_nested_array_removals()
    {
        Livewire::visit(new class extends Component {
            public $data = [
                'items' => [
                    ['id' => 1, 'tags' => ['a', 'b', 'c']],
                    ['id' => 2, 'tags' => ['d', 'e', 'f']],
                    ['id' => 3, 'tags' => ['g', 'h', 'i']],
                ]
            ];

            public function removeMultiple()
            {
                // Remove tags from item 0 AND remove item 2 entirely
                array_pop($this->data['items'][0]['tags']); // removes 'c'
                array_pop($this->data['items'][0]['tags']); // removes 'b'
                array_pop($this->data['items']); // removes item 3
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="remove" wire:click="removeMultiple">Remove Multiple</button>
                        <div dusk="items-count">Items: {{ count($data['items']) }}</div>
                        <div dusk="tags-count">Tags in item 0: {{ count($data['items'][0]['tags']) }}</div>
                        <div dusk="first-tag">First tag: {{ $data['items'][0]['tags'][0] ?? 'none' }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items-count', 'Items: 3')
            ->assertSeeIn('@tags-count', 'Tags in item 0: 3')
            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@items-count', 'Items: 2')
            ->assertSeeIn('@tags-count', 'Tags in item 0: 1')
            ->assertSeeIn('@first-tag', 'First tag: a')
        ;
    }

    private function logDebug($scenario, $debug, $extra = [])
    {
        $logFile = __DIR__ . '/../../../explorations/2026-02-02-merge-snapshot-investigation.md';

        $content = "\n\n---\n\n## $scenario\n\n";
        $content .= "**Timestamp:** " . date('Y-m-d H:i:s') . "\n\n";

        if (!empty($debug)) {
            if (is_array($debug) && isset($debug[0]) && is_array($debug[0])) {
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
                $content .= "- **Dirty key:** `{$r['dirtyKey']}` -> **Root key:** `{$r['rootKey']}`\n";
            }
            $content .= "\n";

            if (!empty($entry['replacements'])) {
                $content .= "### Replacement Details\n\n";
                foreach ($entry['replacements'] as $r) {
                    $content .= "**`{$r['dirtyKey']}`** replaced at root `{$r['rootKey']}`:\n";
                    $content .= "- Old value (before):\n```json\n" . json_encode($r['oldValue'], JSON_PRETTY_PRINT) . "\n```\n";
                    $content .= "- New value (after):\n```json\n" . json_encode($r['newValue'], JSON_PRETTY_PRINT) . "\n```\n\n";
                }
            }
        }

        if (isset($entry['updates']) && !empty($entry['updates'])) {
            $content .= "### Updates (sent TO server)\n\n";
            $content .= "```json\n" . json_encode($entry['updates'], JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        if (isset($entry['ephemeralBefore']) && isset($entry['ephemeralAfter'])) {
            $content .= "### Ephemeral State Comparison\n\n";
            $content .= "**Before merge:**\n```json\n" . json_encode($entry['ephemeralBefore'], JSON_PRETTY_PRINT) . "\n```\n\n";
            $content .= "**After merge:**\n```json\n" . json_encode($entry['ephemeralAfter'], JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        return $content;
    }
}
