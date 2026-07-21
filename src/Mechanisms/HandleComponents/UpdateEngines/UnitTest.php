<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\SnapshotStateStore;
use PHPUnit\Framework\Attributes\DataProvider;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.store', 'array');
        config()->set('livewire.delta.snapshot_store', 'array');
        config()->set('livewire.delta.minimum_html_bytes', 0);
        config()->set('livewire.delta.minimum_savings', 0);
        config()->set('livewire.delta.minimum_compressed_savings_bytes', 0);
        config()->set('livewire.delta.compression_aware', false);
        config()->set('livewire.delta.request_compression', true);
        config()->set('livewire.delta.request_compression_minimum_bytes', 1024);
        config()->set('livewire.delta.snapshot_delta', true);
        config()->set('livewire.delta.snapshot_references', true);
        config()->set('livewire.delta.snapshot_reference_minimum_bytes', 0);
    }

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
        $from = '<div>д字 👋 — ж世</div>';
        $to = '<div>д字 🚀 — з本</div>';

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

    public function test_response_transport_preserves_custom_non_array_responses()
    {
        $this->assertSame(
            'custom-response',
            app(ResponseTransport::class)->encode('custom-response', []),
        );
    }

    public function test_renderless_responses_advertise_the_top_level_request_transport()
    {
        $payload = app(ResponseTransport::class)->encode([
            'components' => [[
                'id' => 'component-id',
                'snapshot' => '{}',
                'effects' => [],
            ]],
        ], [
            'component-id' => [[
                'snapshot' => '{}',
                'render' => ['v' => 1, 'capabilities' => []],
            ]],
        ]);

        $this->assertSame([
            'v' => 1,
            'requestGzip' => 1024,
        ], $payload['transport']);
        $this->assertArrayNotHasKey('html', $payload['components'][0]['effects']);
    }

    public function test_top_level_request_transport_explicitly_disables_gzip_negotiation()
    {
        config()->set('livewire.delta.request_compression', false);

        $payload = app(ResponseTransport::class)->encode(['components' => []], []);

        $this->assertSame([
            'v' => 1,
            'requestGzip' => null,
        ], $payload['transport']);
    }

    public function test_delta_mount_advertises_bounded_client_transport_configuration()
    {
        config()->set('livewire.delta.block_size', 512);
        config()->set('livewire.delta.maximum_manifest_bytes', 2048);
        config()->set('livewire.delta.maximum_fragments', 10);
        config()->set('livewire.delta.cache_accelerator', false);
        config()->set('livewire.delta.snapshot_delta', false);
        config()->set('livewire.delta.snapshot_references', false);

        $component = Livewire::test(DeltaCounter::class);
        $transport = $component->effects['renderTransport'];

        $this->assertSame(1, $transport['v']);
        $this->assertSame(512, $transport['blockSize']);
        $this->assertSame(2048, $transport['maximumManifestBytes']);
        $this->assertSame(10, $transport['maximumFragments']);
        $this->assertFalse($transport['cacheAccelerator']);
        $this->assertFalse($transport['snapshotDelta']);
        $this->assertFalse($transport['snapshotReferences']);
        $this->assertSame(1024 * 1024, $transport['maximumRequestBytes']);
    }

    public function test_v1_transport_sends_full_html_without_a_usable_baseline()
    {
        $html = '<div>'.str_repeat('full-', 1000).'</div>';
        $response = $this->encodeRender($html, [
            'v' => 1,
            'capabilities' => [],
        ]);

        $this->assertSame('full', $response['effects']['render']['mode']);
        $this->assertSame($html, $response['effects']['html']);
        $this->assertSame(strlen($html), $response['effects']['render']['bytes']);
        $this->assertSame(hash('sha256', $html), $response['effects']['render']['target']);
        $this->assertSame(1024, $response['effects']['render']['requestGzip']);
    }

    public function test_v1_transport_can_disable_request_compression_negotiation()
    {
        config()->set('livewire.delta.request_compression', false);

        $response = $this->encodeRender('<div>full</div>', [
            'v' => 1,
            'capabilities' => [],
        ]);

        $this->assertArrayNotHasKey('requestGzip', $response['effects']['render']);
    }

    public function test_legacy_delta_client_is_seeded_when_it_has_no_render_hash_yet()
    {
        $html = '<div>'.str_repeat('legacy-', 1000).'</div>';
        $hash = hash('sha256', $html);
        $response = $this->encodeRender($html, []);

        $this->assertSame($html, $response['effects']['html']);
        $this->assertSame($hash, $response['effects']['htmlHash']);
        $this->assertArrayNotHasKey('render', $response['effects']);
        $this->assertSame(
            $html,
            app(RenderStateStore::class)->get('component-id', $hash),
        );
    }

    public function test_v1_transport_sends_same_when_the_render_is_unchanged()
    {
        $html = '<div>'.str_repeat('same-', 1000).'</div>';
        $response = $this->encodeRender($html, $this->metadata($html, ['same']));

        $this->assertSame('same', $response['effects']['render']['mode']);
        $this->assertArrayNotHasKey('html', $response['effects']);
        $this->assertSame(hash('sha256', $html), $response['effects']['render']['base']);
    }

    public function test_v1_transport_uses_cached_splice_as_an_optional_accelerator()
    {
        $base = '<div>'.str_repeat('stable-content-', 2000).'<span>old</span></div>';
        $target = str_replace('<span>old</span>', '<span>new</span>', $base);
        $hash = hash('sha256', $base);

        app(RenderStateStore::class)->put('component-id', $hash, $base);

        $response = $this->encodeRender($target, $this->metadata($base, ['splice']));
        $render = $response['effects']['render'];

        $this->assertSame('splice', $render['mode']);
        $this->assertArrayNotHasKey('html', $response['effects']);
        $this->assertSame($target, app(HtmlDelta::class)->apply($base, $render['patches']));
    }

    public function test_v1_transport_builds_stateless_chunk_recipes_without_server_memory()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $base = '<main>'.str_repeat('0123456789abcdef', 2000).'<b>old</b></main>';
        $target = str_replace('<b>old</b>', '<b>new</b>', $base);
        $blockSize = 256;
        $metadata = $this->metadata($base, ['chunks']) + [
            'chunks' => [
                'blockSize' => $blockSize,
                'blocks' => app(StatelessHtmlChunks::class)->manifest($base, $blockSize),
            ],
        ];

        $response = $this->encodeRender($target, $metadata);
        $render = $response['effects']['render'];

        $this->assertSame('chunks', $render['mode']);
        $this->assertSame($target, app(StatelessHtmlChunks::class)->apply($base, $render['ops']));
        $this->assertSame(hash('sha256', $target), $render['target']);
    }

    public function test_v1_transport_replaces_explicit_fragments_without_server_memory()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $marker = 'type=transport|name=counter|token=0123456789abcdef|mode=morph';
        $open = '<!--[if FRAGMENT:'.$marker.']><![endif]-->';
        $close = '<!--[if ENDFRAGMENT:'.$marker.']><![endif]-->';
        $stable = str_repeat('<p>stable-content</p>', 1000);
        $base = '<main>'.$stable.$open.'old'.$close.$stable.'</main>';
        $target = '<main>'.$stable.$open.'new'.$close.$stable.'</main>';
        $manifest = app(RenderFragmentTree::class)->manifest($base);

        $response = $this->encodeRender($target, $this->metadata($base, ['fragments']) + [
            'fragments' => $manifest,
        ]);
        $render = $response['effects']['render'];

        $this->assertSame('fragments', $render['mode']);
        $this->assertSame([['0123456789abcdef', 'new']], $render['ops']);
        $this->assertSame($target, app(RenderFragmentTree::class)->apply($base, $render['ops']));
    }

    public function test_configured_manifest_limits_silently_fall_back_to_full_html()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $base = '<main>'.str_repeat('stable-content-', 1000).'<b>old</b></main>';
        $target = str_replace('<b>old</b>', '<b>new</b>', $base);
        $blockSize = 256;
        $chunks = [
            'blockSize' => $blockSize,
            'blocks' => app(StatelessHtmlChunks::class)->manifest($base, $blockSize),
        ];

        config()->set('livewire.delta.maximum_manifest_bytes', 0);

        $chunkResponse = $this->encodeRender(
            $target,
            $this->metadata($base, ['chunks']) + ['chunks' => $chunks],
        );

        $this->assertSame('full', $chunkResponse['effects']['render']['mode']);
        $this->assertSame($target, $chunkResponse['effects']['html']);

        $marker = 'type=transport|name=counter|token=0123456789abcdef|mode=morph';
        $open = '<!--[if FRAGMENT:'.$marker.']><![endif]-->';
        $close = '<!--[if ENDFRAGMENT:'.$marker.']><![endif]-->';
        $fragmentBase = '<main>'.str_repeat('stable-', 1000).$open.'old'.$close.'</main>';
        $fragmentTarget = str_replace('old', 'new', $fragmentBase);

        config()->set('livewire.delta.maximum_fragments', 0);

        $fragmentResponse = $this->encodeRender(
            $fragmentTarget,
            $this->metadata($fragmentBase, ['fragments']) + [
                'fragments' => app(RenderFragmentTree::class)->manifest($fragmentBase),
            ],
        );

        $this->assertSame('full', $fragmentResponse['effects']['render']['mode']);
        $this->assertSame($fragmentTarget, $fragmentResponse['effects']['html']);
    }

    public function test_full_fallback_statistics_include_the_manifest_request_cost()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $marker = 'type=transport|name=counter|token=0123456789abcdef|mode=morph';
        $open = '<!--[if FRAGMENT:'.$marker.']><![endif]-->';
        $close = '<!--[if ENDFRAGMENT:'.$marker.']><![endif]-->';
        $base = '<main>'.str_repeat('stable-', 1000).$open.'old'.$close.'</main>';
        $target = '<section>'.str_repeat('different-', 1000).$open.'new'.$close.'</section>';
        $response = $this->encodeRender(
            $target,
            $this->metadata($base, ['fragments']) + [
                'fragments' => app(RenderFragmentTree::class)->manifest($base),
            ],
        );
        $render = $response['effects']['render'];

        $this->assertSame('full', $render['mode']);
        $this->assertGreaterThan($render['stats']['full'], $render['stats']['selected']);
    }

    public function test_snapshot_delta_is_verified_and_reversible()
    {
        config()->set('livewire.delta.snapshot_references', false);

        $previous = json_encode(['data' => ['body' => str_repeat('stable-', 2000), 'count' => 1]], JSON_THROW_ON_ERROR);
        $target = json_encode(['data' => ['body' => str_repeat('stable-', 2000), 'count' => 2]], JSON_THROW_ON_ERROR);
        $response = $this->encodeRender('<div>ok</div>', [
            'v' => 1,
            'capabilities' => ['snapshot-delta'],
        ], $previous, $target);

        $this->assertArrayNotHasKey('snapshot', $response);
        $this->assertSame(1, $response['snapshotDelta']['v']);
        $this->assertSame(hash('sha256', $previous), $response['snapshotDelta']['base']);

        $reconstructed = app(HtmlDelta::class)->apply($previous, $response['snapshotDelta']['patches']);

        $this->assertSame($target, $reconstructed);
        $this->assertSame(hash('sha256', $reconstructed), $response['snapshotDelta']['target']);
        $this->assertSame(strlen($reconstructed), $response['snapshotDelta']['bytes']);
    }

    public function test_snapshot_references_keep_the_full_snapshot_as_a_client_fallback()
    {
        config()->set('livewire.delta.snapshot_delta', false);

        $snapshot = json_encode(['data' => ['count' => 2]], JSON_THROW_ON_ERROR);
        $response = $this->encodeRender('<div>ok</div>', [
            'v' => 1,
            'capabilities' => ['snapshot-ref'],
        ], '{}', $snapshot);

        $this->assertSame($snapshot, $response['snapshot']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{24}$/', $response['snapshotRef']);
        $this->assertSame(
            $snapshot,
            app(SnapshotStateStore::class)->get($response['snapshotRef'], 'component-id'),
        );
    }

    public function test_snapshot_references_are_not_issued_when_a_full_retry_would_exceed_the_safe_payload_budget()
    {
        config()->set('livewire.delta.snapshot_delta', false);
        config()->set('livewire.payload.max_size', 1024);

        $snapshot = json_encode([
            'data' => str_repeat('A', 600),
        ], JSON_THROW_ON_ERROR);
        $response = $this->encodeRender('<div>ok</div>', [
            'v' => 1,
            'capabilities' => ['snapshot-ref'],
        ], '{}', $snapshot);

        $this->assertGreaterThan(512, strlen($snapshot));
        $this->assertSame($snapshot, $response['snapshot']);
        $this->assertArrayNotHasKey('snapshotRef', $response);
    }

    #[DataProvider('persistentCacheStores')]
    public function test_render_and_snapshot_state_work_with_memory_and_file_cache_stores(string $storeName)
    {
        config()->set('livewire.delta.store', $storeName);
        config()->set('livewire.delta.snapshot_store', $storeName);

        $componentId = 'component-'.str()->random(12);
        $html = '<div>'.str_repeat('cacheable-', 1000).'</div>';
        $hash = hash('sha256', $html);
        $snapshot = json_encode(['memo' => ['id' => $componentId], 'data' => ['value' => 1]], JSON_THROW_ON_ERROR);

        app(RenderStateStore::class)->put($componentId, $hash, $html);
        $reference = app(SnapshotStateStore::class)->put($componentId, $snapshot);

        $this->assertSame($html, app(RenderStateStore::class)->get($componentId, $hash));
        $this->assertSame($snapshot, app(SnapshotStateStore::class)->get($reference, $componentId));
        $this->assertNull(app(SnapshotStateStore::class)->get($reference, 'another-component'));
        $this->assertSame($snapshot, app(SnapshotStateStore::class)->get($reference, $componentId));
    }

    public static function persistentCacheStores(): array
    {
        return [
            'memory' => ['array'],
            'file' => ['file'],
        ];
    }

    public function test_render_cache_preserves_concurrent_immutable_baselines()
    {
        $store = app(RenderStateStore::class);
        $first = '<div>First</div>';
        $second = '<div>Second</div>';
        $firstHash = hash('sha256', $first);
        $secondHash = hash('sha256', $second);

        $store->put('component-id', $firstHash, $first);
        $store->put('component-id', $secondHash, $second);

        $this->assertSame($first, $store->get('component-id', $firstHash));
        $this->assertSame($second, $store->get('component-id', $secondHash));
    }

    public function test_render_cache_rejects_and_forgets_tampered_content()
    {
        $html = '<div>Original render</div>';
        $hash = hash('sha256', $html);
        $key = 'livewire:render:'.hash('sha256', 'component-id'."\0".$hash);

        Cache::store('array')->put($key, [
            'hash' => $hash,
            'bytes' => strlen($html),
            'encoding' => 'identity',
            'payload' => '<div>Tampered render</div>',
        ], 300);

        $this->assertNull(app(RenderStateStore::class)->get('component-id', $hash));
        $this->assertFalse(Cache::store('array')->has($key));
    }

    public function test_testable_livewire_materializes_same_and_snapshot_delta_responses()
    {
        $component = Livewire::test(DeltaCounter::class)
            ->call('increment')
            ->commit()
            ->assertSee('Count: 1');

        $this->assertSame('same', $component->effects['render']['mode']);
        $this->assertArrayHasKey('html', $component->effects);
        $component->assertJsonPath('components.0.snapshotDelta.v', 1);
        $this->assertSame(1, $component->get('count'));
    }

    public function test_testable_livewire_materializes_stateless_chunk_responses()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $component = Livewire::test(new class extends Component {
            public int $count = 0;

            public function increment(): void
            {
                $this->count++;
            }

            public function render()
            {
                return '<div><b>Count: {{ $count }}</b>'.str_repeat('<span>stable-content</span>', 2000).'</div>';
            }
        });

        $component->call('increment');
        $component->call('increment')->assertSee('Count: 2');

        $this->assertSame('chunks', $component->effects['render']['mode']);
        $this->assertArrayHasKey('html', $component->effects);
        $this->assertSame(2, $component->get('count'));
    }

    public function test_testable_livewire_materializes_explicit_fragment_responses()
    {
        config()->set('livewire.delta.cache_accelerator', false);

        $component = Livewire::test(new class extends Component {
            public int $count = 0;

            public function increment(): void
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <main>
                    {{ str_repeat('stable-content-', 2000) }}
                    @fragment('counter')
                        <b>Count: {{ $count }}</b>
                    @endfragment
                </main>
                HTML;
            }
        });

        $component->call('increment');
        $component->call('increment')->assertSee('Count: 2');

        $this->assertSame('fragments', $component->effects['render']['mode']);
        $this->assertArrayHasKey('html', $component->effects);
    }

    public function test_renderless_updates_preserve_the_last_render_baseline()
    {
        $component = Livewire::test(DeltaCounter::class)
            ->call('increment')
            ->call('increment')
            ->call('incrementRenderless')
            ->assertSee('Count: 2');

        $this->assertArrayNotHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('htmlDelta', $component->effects);

        $component->call('increment')->assertSee('Count: 4');

        $this->assertArrayHasKey('render', $component->effects);
        $this->assertSame('splice', $component->effects['render']['mode']);
    }

    public function test_morph_remains_the_default_update_engine()
    {
        config()->set('livewire.update_engine', 'morph');

        $component = Livewire::test(DeltaCounter::class);

        $this->assertArrayNotHasKey('renderTransport', $component->effects);

        $component
            ->call('increment')
            ->call('increment');

        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('render', $component->effects);
    }

    protected function metadata(string $html, array $capabilities): array
    {
        return [
            'v' => 1,
            'capabilities' => $capabilities,
            'base' => [
                'hash' => hash('sha256', $html),
                'bytes' => strlen($html),
                'revision' => 1,
            ],
        ];
    }

    protected function encodeRender(
        string $html,
        array $metadata,
        string $previousSnapshot = '{}',
        string $snapshot = '{}',
    ): array {
        $payload = [
            'components' => [[
                'id' => 'component-id',
                'snapshot' => $snapshot,
                'effects' => ['html' => $html],
            ]],
        ];
        $contexts = [
            'component-id' => [
                'snapshot' => $previousSnapshot,
                'render' => $metadata,
            ],
        ];

        return app(ResponseTransport::class)->encode($payload, $contexts)['components'][0];
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
