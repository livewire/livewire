<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class UnitTest extends \Tests\TestCase
{
    public function test_it_encodes_replacements_as_reversible_byte_deltas()
    {
        $delta = new HtmlDelta;
        $from = '<div>'.str_repeat('before-', 20).'old'.str_repeat('-after', 20).'</div>';
        $to = '<div>'.str_repeat('before-', 20).'new'.str_repeat('-after', 20).'</div>';

        $patches = $delta->encode($from, $to);
        $patch = $patches[0];

        $this->assertCount(1, $patches);
        $this->assertSame($to, $delta->apply($from, $patches));
        $this->assertSame(3, $patch['delete']);
        $this->assertSame(base64_encode('new'), $patch['insert']);
    }

    public function test_it_preserves_unicode_when_a_delta_boundary_is_inside_a_multibyte_character()
    {
        $delta = new HtmlDelta;
        $from = '<div>foo 👋 — bar</div>';
        $to = '<div>foo 🚀 — baz</div>';

        $patches = $delta->encode($from, $to);

        $this->assertSame($to, $delta->apply($from, $patches));
    }

    public function test_it_encodes_distant_changes_as_multiple_patches()
    {
        $delta = new HtmlDelta;
        $card = '<article wire:key="card-3">Deploy application</article>';
        $middle = str_repeat('<article>Unchanged card content</article>', 100);
        $from = '<div><section>'.$card.$middle.'</section><section>Done</section></div>';
        $to = '<div><section>'.$middle.'</section><section>Done'.$card.'</section></div>';

        $patches = $delta->encode($from, $to);

        $this->assertGreaterThanOrEqual(2, count($patches));
        $this->assertSame($to, $delta->apply($from, $patches));
    }

    public function test_it_rejects_overlapping_patches()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new HtmlDelta)->apply('<div>content</div>', [
            ['start' => 5, 'delete' => 3, 'insert' => ''],
            ['start' => 6, 'delete' => 1, 'insert' => ''],
        ]);
    }

    public function test_it_bounds_anchor_scanning_for_completely_different_html()
    {
        $delta = new HtmlDelta;
        $from = '<div>'.str_repeat('A', 10000).'</div>';
        $to = '<div>'.str_repeat('B', 10000).'</div>';

        $patches = $delta->encode($from, $to);

        $this->assertCount(1, $patches);
        $this->assertSame($to, $delta->apply($from, $patches));
    }

    public function test_delta_engine_seeds_with_full_html_then_sends_compact_json_deltas()
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.minimum_savings', 0.1);
        config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);

        $component = Livewire::test(new class extends Component {
            public int $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="increment">Increment</button>
                    <span>Count: {{ $count }}</span>
                    <p>{{ str_repeat('stable-content-', 1000) }}</p>
                </div>
                HTML;
            }
        });

        $component->call('increment')->assertSee('Count: 1');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);

        $fullHtmlSize = strlen($component->effects['html']);

        $component->call('increment')->assertSee('Count: 2');

        $this->assertArrayNotHasKey('html', $component->effects);
        $this->assertArrayHasKey('htmlDelta', $component->effects);
        $this->assertIsArray($component->effects['htmlDelta']['patches']);
        $this->assertLessThan($fullHtmlSize / 10, strlen(json_encode($component->effects['htmlDelta'])));
    }

    public function test_delta_engine_rejects_a_delta_that_is_larger_after_gzip()
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        config()->set('livewire.delta.minimum_savings', 0.1);
        config()->set('livewire.delta.compression_aware', true);
        config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);

        $deltas = new HtmlDelta;
        $randomLooking = implode('', array_map(
            fn ($index) => hash('sha256', (string) $index),
            range(1, 16),
        ));
        $from = '<div>'.str_repeat('A', 5000).'</div>';
        $to = '<div>'.str_repeat('A', 2000).$randomLooking.str_repeat('A', 1976).'</div>';
        $hash = $deltas->hash($to);
        $effect = [
            'base' => $deltas->hash($from),
            'patches' => $deltas->encode($from, $to),
        ];
        $deltaPayload = json_encode([
            'htmlDelta' => $effect,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);
        $fullPayload = json_encode([
            'html' => $to,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);
        $engine = new InspectableDeltaUpdateEngine(
            new UnavailableRenderStateStore,
            $deltas,
        );

        $this->assertLessThan(strlen($fullPayload) * 0.9, strlen($deltaPayload));
        $this->assertGreaterThanOrEqual(strlen(gzencode($fullPayload, 1)) * 0.9, strlen(gzencode($deltaPayload, 1)));
        $this->assertFalse($engine->canSendDelta($effect, $to, $hash));

        config()->set('livewire.delta.compression_aware', false);

        $this->assertTrue($engine->canSendDelta($effect, $to, $hash));
    }

    public function test_delta_engine_requires_minimum_absolute_compressed_savings()
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        config()->set('livewire.delta.minimum_savings', 0.1);
        config()->set('livewire.delta.compression_aware', true);

        $deltas = new HtmlDelta;
        $rows = implode('', array_map(
            fn ($index) => '<tr><td>'.$index.'</td><td>'.hash('sha256', (string) $index).'</td></tr>',
            range(1, 200),
        ));
        $from = '<table>'.$rows.'<tfoot><tr><td>pending</td></tr></tfoot></table>';
        $to = '<table>'.$rows.'<tfoot><tr><td>running</td></tr></tfoot></table>';
        $hash = $deltas->hash($to);
        $effect = [
            'base' => $deltas->hash($from),
            'patches' => $deltas->encode($from, $to),
        ];
        $deltaPayload = json_encode([
            'htmlDelta' => $effect,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);
        $fullPayload = json_encode([
            'html' => $to,
            'htmlHash' => $hash,
        ], JSON_THROW_ON_ERROR);
        $compressedSavings = strlen(gzencode($fullPayload, 1))
            - strlen(gzencode($deltaPayload, 1));
        $engine = new InspectableDeltaUpdateEngine(
            new UnavailableRenderStateStore,
            $deltas,
        );

        $this->assertGreaterThan(0, $compressedSavings);

        config()->set('livewire.delta.minimum_compressed_savings_bytes', $compressedSavings + 1);

        $this->assertFalse($engine->canSendDelta($effect, $to, $hash));

        config()->set('livewire.delta.minimum_compressed_savings_bytes', $compressedSavings);

        $this->assertTrue($engine->canSendDelta($effect, $to, $hash));
    }

    public function test_small_renders_use_morph_without_touching_render_state()
    {
        config()->set('livewire.delta.minimum_html_bytes', 8192);

        $states = new TrackingRenderStateStore;
        $deltas = new HtmlDelta;
        $engine = new DeltaUpdateEngine($states, $deltas);
        $component = new DeltaCounter;
        $component->setId('component-id');
        $mountContext = new ComponentContext($component, mounting: true);
        $previousHtml = '<div>Count: 1</div>';
        $previousHash = $deltas->hash($previousHtml);

        $engine->mount($component, $previousHtml, $mountContext);

        $this->assertArrayNotHasKey('delta', $mountContext->memo);

        $context = new ComponentContext($component);

        $engine->update(
            $component,
            '<div>Count: 2</div>',
            ['delta' => ['revision' => 1, 'hash' => $previousHash]],
            $context,
            ['htmlHash' => $previousHash],
        );

        $this->assertSame('<div>Count: 2</div>', $context->effects['html']);
        $this->assertArrayNotHasKey('htmlHash', $context->effects);
        $this->assertArrayNotHasKey('delta', $context->memo);
        $this->assertSame(0, $states->getCalls);
        $this->assertSame(0, $states->putCalls);
    }

    public function test_components_can_cross_the_hybrid_size_boundary()
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.minimum_html_bytes', 1024);
        config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);

        $component = Livewire::test(new class extends Component {
            public int $count = 0;

            public bool $large = false;

            public function increment()
            {
                $this->count++;
            }

            public function grow()
            {
                $this->large = true;
            }

            public function shrink()
            {
                $this->large = false;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span>Count: {{ $count }}</span>
                    @if ($large)
                        <p>{{ str_repeat('stable-content-', 1000) }}</p>
                    @endif
                </div>
                HTML;
            }
        });

        $component->call('grow');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayHasKey('htmlHash', $component->effects);

        $component->call('increment')->assertSee('Count: 1');

        $this->assertArrayHasKey('htmlDelta', $component->effects);

        $component->call('shrink');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);
        $this->assertArrayNotHasKey('htmlHash', $component->effects);

        $component->call('grow');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);
        $this->assertArrayHasKey('htmlHash', $component->effects);
    }

    public function test_delta_engine_falls_back_to_full_html_after_the_render_state_expires()
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.minimum_html_bytes', 0);

        $component = Livewire::test(DeltaCounter::class);

        $component->call('increment');

        Cache::store('array')->flush();

        $component->call('increment')->assertSee('Count: 2');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);
    }

    public function test_delta_engine_falls_back_to_full_html_when_the_cached_render_fails_integrity()
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.minimum_html_bytes', 0);

        $component = Livewire::test(DeltaCounter::class);

        $component->call('increment');

        Cache::store('array')->put(
            'livewire:delta:'.$component->instance()->getId(),
            [
                'hash' => $component->effects['htmlHash'],
                'html' => '<div>Tampered render</div>',
            ],
            300,
        );

        $component->call('increment')->assertSee('Count: 2');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);
    }

    public function test_delta_engine_falls_back_to_full_html_when_the_store_is_unavailable()
    {
        config()->set('livewire.delta.minimum_html_bytes', 0);

        $deltas = new HtmlDelta;
        $engine = new DeltaUpdateEngine(new UnavailableRenderStateStore, $deltas);
        $component = new DeltaCounter;
        $component->setId('component-id');
        $context = new ComponentContext($component);
        $previousHtml = '<div>Count: 1</div>';
        $previousHash = $deltas->hash($previousHtml);

        $engine->update(
            $component,
            '<div>Count: 2</div>',
            ['delta' => ['revision' => 1, 'hash' => $previousHash]],
            $context,
            ['htmlHash' => $previousHash],
        );

        $this->assertSame('<div>Count: 2</div>', $context->effects['html']);
        $this->assertArrayNotHasKey('htmlDelta', $context->effects);
    }

    public function test_cache_store_only_retains_the_latest_render_for_a_component()
    {
        config()->set('livewire.delta.store', 'array');

        $store = app(RenderStateStore::class);

        $store->put('component-id', 'first-hash', '<div>First</div>');
        $store->put('component-id', 'second-hash', '<div>Second</div>');

        $this->assertNull($store->get('component-id', 'first-hash'));
        $this->assertSame('<div>Second</div>', $store->get('component-id', 'second-hash'));
    }

    public function test_cache_store_rejects_rendered_html_that_does_not_match_its_hash()
    {
        config()->set('livewire.delta.store', 'array');

        $html = '<div>Original render</div>';
        $hash = app(HtmlDelta::class)->hash($html);
        $key = 'livewire:delta:component-id';

        Cache::store('array')->put($key, [
            'hash' => $hash,
            'html' => '<div>Tampered render</div>',
        ], 300);

        $this->assertNull(app(RenderStateStore::class)->get('component-id', $hash));
        $this->assertFalse(Cache::store('array')->has($key));
    }

    public function test_renderless_updates_preserve_the_last_render_baseline()
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.minimum_html_bytes', 0);
        config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);

        $component = Livewire::test(DeltaCounter::class)
            ->call('increment')
            ->call('incrementRenderless')
            ->assertSee('Count: 1');

        $this->assertArrayNotHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);

        $component->call('increment')->assertSee('Count: 3');

        $this->assertArrayHasKey('htmlDelta', $component->effects);
    }

    public function test_morph_remains_the_default_update_engine()
    {
        $component = Livewire::test(DeltaCounter::class)
            ->call('increment')
            ->call('increment');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);
    }
}

class DeltaCounter extends Component
{
    public int $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function incrementRenderless()
    {
        $this->count++;
        $this->skipRender();
    }

    public function render()
    {
        return '<div>Count: {{ $count }}'.str_repeat('<span>stable</span>', 100).'</div>';
    }
}

class UnavailableRenderStateStore implements RenderStateStore
{
    public function get(string $componentId, string $hash): ?string
    {
        throw new \RuntimeException('Render state store unavailable.');
    }

    public function put(string $componentId, string $hash, string $html): void
    {
        throw new \RuntimeException('Render state store unavailable.');
    }
}

class TrackingRenderStateStore implements RenderStateStore
{
    public int $getCalls = 0;

    public int $putCalls = 0;

    public function get(string $componentId, string $hash): ?string
    {
        $this->getCalls++;

        return null;
    }

    public function put(string $componentId, string $hash, string $html): void
    {
        $this->putCalls++;
    }
}

class InspectableDeltaUpdateEngine extends DeltaUpdateEngine
{
    public function canSendDelta(array $effect, string $html, string $hash): bool
    {
        return $this->shouldSendDelta($effect, $html, $hash);
    }
}
