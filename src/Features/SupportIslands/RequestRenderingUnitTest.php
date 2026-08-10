<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use Tests\TestCase;

class RequestRenderingUnitTest extends TestCase
{
    public function test_automatic_morph_batches_render_final_component_state_once()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment'),
            $this->islandCall('addTen'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 11'],
        ]);
    }

    public function test_renderless_attributes_and_modifiers_are_soft_per_call_opt_outs()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('incrementRenderless'),
            $this->islandCall('increment'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 2'],
        ]);

        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment'),
            $this->islandCall('incrementRenderless'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 2'],
        ]);

        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment', renderless: true),
            $this->islandCall('increment'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 2'],
        ]);

        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment'),
            $this->islandCall('increment', renderless: true),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 2'],
        ]);
    }

    public function test_all_soft_renderless_island_calls_produce_no_automatic_fragment()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('incrementRenderless'),
            $this->islandCall('addTen', renderless: true),
        ]);

        $this->assertSame([], $component->effects['islandFragments'] ?? []);
    }

    public function test_soft_ordered_calls_do_not_force_an_ordered_strategy()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment'),
            $this->islandCall('addTen', mode: 'append', renderless: true),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 11'],
        ]);
    }

    public function test_append_and_prepend_batches_capture_each_action_in_order()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment', mode: 'append'),
            $this->islandCall('addTen', mode: 'prepend'),
        ]);

        $this->assertFragments($component, [
            ['mode=append', 'State 1'],
            ['mode=prepend', 'State 11'],
        ]);
    }

    public function test_morphs_join_the_ordered_timeline_when_modes_are_mixed()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment'),
            $this->islandCall('addTen', mode: 'append'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 1'],
            ['mode=append', 'State 11'],
        ]);

        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment', mode: 'append'),
            $this->islandCall('addTen'),
        ]);

        $this->assertFragments($component, [
            ['mode=append', 'State 1'],
            ['mode=morph', 'State 11'],
        ]);
    }

    public function test_hard_skip_vetoes_the_entire_automatic_target_scope()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment', mode: 'append'),
            $this->islandCall('hardSkip', mode: 'append'),
        ]);

        $this->assertSame([], $component->effects['islandFragments'] ?? []);

        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('hardSkip'),
            $this->islandCall('increment'),
        ]);

        $this->assertSame([], $component->effects['islandFragments'] ?? []);
    }

    public function test_hard_skip_only_vetoes_its_named_island_target()
    {
        $component = $this->testComponent();

        $component->update(calls: [
            $this->islandCall('hardSkip', name: 'counter'),
            $this->islandCall('increment', name: 'other'),
        ]);

        $this->assertFragments($component, [
            ['name=other', 'State 2'],
        ]);
    }

    public function test_repeated_explicit_render_commands_are_all_preserved()
    {
        $component = $this->testComponent();

        $component->call('renderExplicitlyThreeTimes');

        $this->assertFragments($component, [
            ['mode=morph', 'State 1'],
            ['mode=morph', 'State 2'],
            ['mode=morph', 'State 3'],
        ]);

        $component = $this->testComponent();

        $component->call('appendExplicitlyThreeTimes');

        $this->assertFragments($component, [
            ['mode=append', 'State 1'],
            ['mode=append', 'State 2'],
            ['mode=append', 'State 3'],
        ]);
    }

    public function test_explicit_render_is_preserved_inside_a_hard_veto()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('hardSkipWithExplicitRender'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 1'],
        ]);
    }

    public function test_explicit_render_precedes_the_final_automatic_morph()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('explicitThenIncrement'),
        ]);

        $this->assertFragments($component, [
            ['mode=morph', 'State 1'],
            ['mode=morph', 'State 2'],
        ]);
    }

    public function test_explicit_rendering_before_the_call_batch_uses_the_same_fragment_collection()
    {
        $component = $this->testComponent();

        $component->update(
            calls: [$this->islandCall('increment')],
            updates: ['count' => 1],
        );

        $this->assertFragments($component, [
            ['mode=morph', 'State 1'],
            ['mode=morph', 'State 2'],
        ]);
    }

    public function test_one_logical_render_updates_every_linked_island_token()
    {
        $component = $this->testComponent();

        $this->updateIsland($component, [
            $this->islandCall('increment', name: 'linked'),
        ]);

        $fragments = $component->effects['islandFragments'] ?? [];

        $this->assertCount(2, $fragments);
        $this->assertStringContainsString('State 1', $fragments[0]);
        $this->assertStringContainsString('State 1', $fragments[1]);
    }

    protected function testComponent()
    {
        return Livewire::test(new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function addTen()
            {
                $this->count += 10;
            }

            #[Renderless]
            public function incrementRenderless()
            {
                $this->count++;
            }

            public function hardSkip()
            {
                $this->count++;
                $this->skipRender();
            }

            public function renderExplicitlyThreeTimes()
            {
                foreach ([1, 2, 3] as $count) {
                    $this->count = $count;
                    $this->renderIsland('counter');
                }
            }

            public function appendExplicitlyThreeTimes()
            {
                foreach ([1, 2, 3] as $count) {
                    $this->count = $count;
                    $this->renderIsland('counter', mode: 'append');
                }
            }

            public function hardSkipWithExplicitRender()
            {
                $this->count++;
                $this->skipRender();
                $this->renderIsland('counter');
            }

            public function explicitThenIncrement()
            {
                $this->count++;
                $this->renderIsland('counter');
                $this->count++;
            }

            public function updatedCount()
            {
                $this->renderIsland('counter');
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    @island(name: 'counter')<div>State {{ $count }}</div>@endisland
                    @island(name: 'other')<div>State {{ $count }}</div>@endisland
                    @island(name: 'linked')<div>State {{ $count }}</div>@endisland
                    @island(name: 'linked')<div>State {{ $count }}</div>@endisland
                </div>
                HTML;
            }
        });
    }

    protected function updateIsland($component, $calls)
    {
        $component->update(calls: $calls);
    }

    protected function islandCall($method, $mode = 'morph', $renderless = false, $name = 'counter')
    {
        return [
            'method' => $method,
            'params' => [],
            'metadata' => [
                'island' => [
                    'name' => $name,
                    'mode' => $mode,
                ],
                ...($renderless ? ['renderless' => true] : []),
            ],
        ];
    }

    protected function assertFragments($component, $expectedFragments)
    {
        $fragments = $component->effects['islandFragments'] ?? [];

        $this->assertCount(count($expectedFragments), $fragments);

        foreach ($expectedFragments as $index => $needles) {
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $fragments[$index]);
            }
        }
    }
}
