<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

class TransportPrimitivesUnitTest extends \Tests\TestCase
{
    public function test_stateless_chunk_manifest_has_a_pinned_network_byte_order()
    {
        $chunks = new StatelessHtmlChunks;

        $this->assertSame('DfgDJK7vKlA=', $chunks->manifest('abcdefgh', 256));
        $this->assertSame([[
            'offset' => 0,
            'length' => 8,
            'weak' => 0x0df80324,
            'strong' => 0xaeef2a50,
        ]], $chunks->decodeManifest('DfgDJK7vKlA=', 8, 256));
    }

    public function test_stateless_chunks_reconstruct_unicode_after_insertions_and_deletions()
    {
        $chunks = new StatelessHtmlChunks;
        $blockSize = 256;
        $base = '<main>'
            .str_repeat('<p>foo 👋</p>', 20)
            .'<p>bar baz</p>'
            .str_repeat('<p>More stable content</p>', 20)
            .'</main>';
        $target = '<main><h1>qux 🚀</h1>'
            .str_repeat('<p>foo 👋</p>', 20)
            .str_repeat('<p>More stable content</p>', 20)
            .'</main>';

        $manifest = $chunks->manifest($base, $blockSize);
        $ops = $chunks->encode($target, $manifest, strlen($base), $blockSize);

        $this->assertSame($target, $chunks->apply($base, $ops));
        $this->assertContains('c', array_column($ops, 0));
        $this->assertContains('a', array_column($ops, 0));
    }

    public function test_stateless_chunks_reconstruct_reordered_and_duplicate_blocks()
    {
        $chunks = new StatelessHtmlChunks;
        $blockSize = 256;
        $a = str_repeat('A', $blockSize);
        $b = str_repeat('B', $blockSize);
        $c = str_repeat('C', $blockSize);
        $tail = 'partial';
        $base = $a.$b.$c.$b.$tail;
        $target = $c.$b.$a.$b.$b.$tail;

        $ops = $chunks->encode(
            $target,
            $chunks->manifest($base, $blockSize),
            strlen($base),
            $blockSize,
        );

        $this->assertSame($target, $chunks->apply($base, $ops));
        $this->assertNotEmpty(array_filter($ops, fn ($op) => $op[0] === 'c'));
    }

    public function test_stateless_chunks_merge_adjacent_copy_operations()
    {
        $chunks = new StatelessHtmlChunks;
        $blockSize = 256;
        $base = str_repeat('0123456789abcdef', 8).'tail';

        $ops = $chunks->encode(
            $base,
            $chunks->manifest($base, $blockSize),
            strlen($base),
            $blockSize,
        );

        $this->assertSame([['c', 0, strlen($base)]], $ops);
    }

    public function test_stateless_chunks_reject_malformed_manifests_and_recipe_bounds()
    {
        $chunks = new StatelessHtmlChunks;

        try {
            $chunks->decodeManifest('not canonical base64!', 512, 256);
            $this->fail('The malformed manifest should have been rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Invalid HTML chunk manifest.', $exception->getMessage());
        }

        try {
            $chunks->decodeManifest(base64_encode(str_repeat("\0", 8)), 512, 256);
            $this->fail('The truncated manifest should have been rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame(
                'HTML chunk manifest length does not match its baseline.',
                $exception->getMessage(),
            );
        }

        $this->expectException(\InvalidArgumentException::class);

        $chunks->apply('short', [['c', 2, 20]]);
    }

    public function test_stateless_chunks_bound_work_for_adversarial_checksum_buckets()
    {
        $chunks = new StatelessHtmlChunks;
        $blockSize = 65536;
        $validSignature = base64_decode(
            $chunks->manifest(str_repeat('A', $blockSize), $blockSize),
            strict: true,
        );
        $wrongStrongChecksum = substr($validSignature, 0, 4).pack('N', 0);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('checksum work exceeded its byte limit');

        $chunks->encode(
            str_repeat('A', $blockSize + 256),
            base64_encode($wrongStrongChecksum),
            $blockSize,
            $blockSize,
        );
    }

    public function test_fragment_tree_selects_only_a_changed_nested_fragment()
    {
        $fragments = new RenderFragmentTree;
        $base = '<main>before'
            .$this->fragment('outer', '<p>outer</p>'.$this->fragment('inner', '<span>old</span>'))
            .'after</main>';
        $target = '<main>before'
            .$this->fragment('outer', '<p>outer</p>'.$this->fragment('inner', '<span>new 🚀</span>'))
            .'after</main>';

        $manifest = $fragments->manifest($base);
        $ops = $fragments->encode($target, $manifest);

        $this->assertSame([['inner', '<span>new 🚀</span>']], $ops);
        $this->assertSame($target, $fragments->apply($base, $ops));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $manifest['root']);
        $this->assertSame(['outer', 'inner'], array_column($manifest['nodes'], 0));
    }

    public function test_fragment_tree_selects_a_parent_when_its_skeleton_changes()
    {
        $fragments = new RenderFragmentTree;
        $baseInner = '<p>before</p>'.$this->fragment('inner', '<span>old</span>');
        $targetInner = '<p>after</p>'.$this->fragment('inner', '<span>new</span>');
        $base = '<main>'.$this->fragment('outer', $baseInner).'</main>';
        $target = '<main>'.$this->fragment('outer', $targetInner).'</main>';

        $ops = $fragments->encode($target, $fragments->manifest($base));

        $this->assertSame([['outer', $targetInner]], $ops);
        $this->assertSame($target, $fragments->apply($base, $ops));
    }

    public function test_fragment_tree_returns_null_when_content_outside_fragments_changes()
    {
        $fragments = new RenderFragmentTree;
        $base = '<main>before'.$this->fragment('item', '<p>same</p>').'</main>';
        $target = '<main>after'.$this->fragment('item', '<p>same</p>').'</main>';

        $this->assertNull($fragments->encode($target, $fragments->manifest($base)));
    }

    public function test_fragment_tree_rejects_duplicate_and_unbalanced_transport_markers()
    {
        $fragments = new RenderFragmentTree;
        $duplicate = $this->fragment('same', 'one').$this->fragment('same', 'two');
        $unbalanced = $this->startFragment('open').'<p>never closed</p>';
        $crossed = '<!--[if FRAGMENT:type=slot|token=slot]><![endif]-->'
            .$this->startFragment('transport')
            .'content'
            .'<!--[if ENDFRAGMENT:type=slot|token=slot]><![endif]-->'
            .$this->endFragment('transport');

        $this->assertNull($fragments->manifest($duplicate));
        $this->assertNull($fragments->manifest($unbalanced));
        $this->assertNull($fragments->manifest($crossed));
    }

    public function test_fragment_tree_rejects_overlapping_operations()
    {
        $fragments = new RenderFragmentTree;
        $base = '<main>'.$this->fragment(
            'outer',
            '<p>outer</p>'.$this->fragment('inner', '<p>inner</p>'),
        ).'</main>';

        $this->expectException(\InvalidArgumentException::class);

        $fragments->apply($base, [
            ['outer', '<p>changed outer</p>'],
            ['inner', '<p>changed inner</p>'],
        ]);
    }

    public function test_candidate_selector_chooses_a_smaller_candidate_after_manifest_tax()
    {
        $selector = new RenderCandidateSelector(
            minimumRelativeSavings: 0.1,
            minimumAbsoluteSavingsBytes: 20,
            compressionAware: false,
        );
        $full = ['mode' => 'full', 'html' => str_repeat('stable-', 500)];
        $chunks = ['mode' => 'chunks', 'ops' => [['c', 0, 3500]]];

        $this->assertSame($chunks, $selector->select($full, [$chunks], requestManifestTax: 64));
        $this->assertSame($full, $selector->select($full, [$chunks], requestManifestTax: 5000));
    }

    public function test_candidate_selector_falls_back_to_full_when_gzip_erases_raw_savings()
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        $selector = new RenderCandidateSelector(
            minimumRelativeSavings: 0.1,
            minimumAbsoluteSavingsBytes: 0,
            compressionAware: true,
        );
        $full = ['mode' => 'full', 'html' => str_repeat('A', 10000)];
        $noise = implode('', array_map(
            fn ($index) => hash('sha256', (string) $index),
            range(1, 8),
        ));
        $chunks = ['mode' => 'chunks', 'ops' => [['a', base64_encode($noise)]]];

        $this->assertLessThan($selector->sizes($full)['raw'], $selector->sizes($chunks)['raw']);
        $this->assertGreaterThan($selector->sizes($full)['gzip'], $selector->sizes($chunks)['gzip']);
        $this->assertSame($full, $selector->select($full, [$chunks]));
    }

    protected function fragment(string $token, string $html): string
    {
        return $this->startFragment($token).$html.$this->endFragment($token);
    }

    protected function startFragment(string $token): string
    {
        return "<!--[if FRAGMENT:type=transport|token={$token}]><![endif]-->";
    }

    protected function endFragment(string $token): string
    {
        return "<!--[if ENDFRAGMENT:type=transport|token={$token}]><![endif]-->";
    }
}
