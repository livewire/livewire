<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class UnitTest extends \Tests\TestCase
{
    public function test_reactive_prop_value_is_available_during_boot_hydrate_and_booted_hooks()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 0]);
        $this->assertEquals(0, $child->get('count'));

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(5, $child->get('bootValue'), 'boot() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('hydrateValue'), 'hydrate() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('bootedValue'), 'booted() should see the new reactive prop value');
    }

    public function test_updating_hook_sees_old_value_and_updated_hook_sees_new_value_for_reactive_props()
    {
        Livewire::component('child-with-update-hooks', ChildWithUpdateHooks::class);

        $child = Livewire::test(ChildWithUpdateHooks::class, ['count' => 0]);

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(0, $child->get('oldValueDuringUpdating'), 'updatingCount() should see the old value via $this->count');
        $this->assertEquals(5, $child->get('newValueDuringUpdated'), 'updatedCount() should see the new value via $this->count');
    }

    public function test_stale_parent_snapshots_with_rapid_updates_do_not_throw_reactive_mutation_exceptions()
    {
        Livewire::component('reactive-facets-child', ReactiveFacetsChild::class);

        $parent = Livewire::test(ReactiveFacetsParent::class);

        $snapshotJson = json_encode($parent->snapshot, JSON_THROW_ON_ERROR);
        $childSnapshotJson = $this->extractSnapshotJsonForComponent($parent->html(), 'reactive-facets-child');

        $this->withHeaders(['X-Livewire' => 'true'])->postJson(
            EndpointResolver::updatePath(),
            ['components' => [
                [
                    'snapshot' => $snapshotJson,
                    'updates' => ['search' => 'b'],
                    'calls' => [],
                ],
                [
                    'snapshot' => $childSnapshotJson,
                    'updates' => [],
                    'calls' => [],
                ],
            ]],
        )->assertOk();

        $this->withHeaders(['X-Livewire' => 'true'])->postJson(
            EndpointResolver::updatePath(),
            ['components' => [
                [
                    'snapshot' => $snapshotJson,
                    'updates' => ['search' => 'bo'],
                    'calls' => [],
                ],
                [
                    'snapshot' => $childSnapshotJson,
                    'updates' => [],
                    'calls' => [],
                ],
            ]],
        )->assertOk();

        $this->withHeaders(['X-Livewire' => 'true'])->postJson(
            EndpointResolver::updatePath(),
            ['components' => [
                [
                    'snapshot' => $snapshotJson,
                    'updates' => ['search' => 'bol'],
                    'calls' => [],
                ],
                [
                    'snapshot' => $childSnapshotJson,
                    'updates' => [],
                    'calls' => [],
                ],
            ]],
        )->assertOk();
    }

    private function extractSnapshotJsonForComponent(string $html, string $componentName): string
    {
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $rawSnapshot) {
            $decodedSnapshot = json_decode(html_entity_decode($rawSnapshot, ENT_QUOTES | ENT_HTML5), true);

            if (($decodedSnapshot['memo']['name'] ?? null) === $componentName) {
                return json_encode($decodedSnapshot, JSON_THROW_ON_ERROR);
            }
        }

        $this->fail('Unable to find child component snapshot in initial render HTML.');
    }
}

class ChildWithLifecycleHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $bootValue = 0;
    public $hydrateValue = 0;
    public $bootedValue = 0;

    public function boot()
    {
        $this->bootValue = $this->count;
    }

    public function hydrate()
    {
        $this->hydrateValue = $this->count;
    }

    public function booted()
    {
        $this->bootedValue = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class ChildWithUpdateHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $oldValueDuringUpdating = null;
    public $newValueDuringUpdated = null;

    public function updatingCount($value)
    {
        // $this->count should still be the OLD value at this point
        $this->oldValueDuringUpdating = $this->count;
    }

    public function updatedCount($value)
    {
        // $this->count should be the NEW value at this point
        $this->newValueDuringUpdated = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class ReactiveFacetsParent extends Component
{
    public string $search = '';

    public array $facets = [
        'brand' => [
            'label' => 'Brand',
            'values' => [
                ['value' => 'Brembo', 'count' => 2],
                ['value' => 'Bolt', 'count' => 3],
            ],
        ],
        'query' => [
            'label' => 'Query',
            'values' => [
                ['value' => '', 'count' => 1],
            ],
        ],
    ];

    public function updatedSearch(): void
    {
        $this->facets['query']['values'][0]['value'] = $this->search;
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <input type="text" wire:model.live.debounce.10ms="search">
                <livewire:reactive-facets-child :facets="$facets" />
            </div>
        HTML;
    }
}

class ReactiveFacetsChild extends Component
{
    #[BaseReactive]
    public array $facets = [];

    public function render()
    {
        return <<<'HTML'
            <div>
                @foreach ($facets as $facet)
                    <div>{{ $facet['label'] }}</div>
                @endforeach
            </div>
        HTML;
    }
}
