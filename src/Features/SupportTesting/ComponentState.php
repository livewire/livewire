<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\UpdateEngines\RenderFragmentTree;
use Livewire\Mechanisms\HandleComponents\UpdateEngines\StatelessHtmlChunks;

class ComponentState
{
    function __construct(
        protected $component,
        protected $response,
        protected $view,
        protected $html,
        protected $snapshot,
        protected $effects,
        protected $serverRenderedHtml = null,
        protected $serverRenderedHtmlHash = null,
        protected int $renderRevision = 0,
    ) {}

    function getComponent() {
        return $this->component;
    }

    function getSnapshot()
    {
        return $this->snapshot;
    }

    function getSnapshotData()
    {
        return $this->untupleify($this->snapshot['data']);
    }

    function getEffects()
    {
        return $this->effects;
    }

    function getServerRenderedHtml()
    {
        return $this->serverRenderedHtml;
    }

    function getServerRenderedHtmlHash()
    {
        return $this->serverRenderedHtmlHash;
    }

    function getRenderRevision()
    {
        return $this->renderRevision;
    }

    function getRenderMetadata()
    {
        if (config('livewire.update_engine', 'morph') !== 'delta') return [];

        $blockSize = max(256, min(65536, (int) config('livewire.delta.block_size', 2048)));
        $metadata = [
            'v' => 1,
            'capabilities' => ['same'],
        ];

        if (config('livewire.delta.snapshot_delta', true)) {
            $metadata['capabilities'][] = 'snapshot-delta';
        }

        if (config('livewire.delta.snapshot_references', false)) {
            $metadata['capabilities'][] = 'snapshot-ref';
        }

        if ($this->serverRenderedHtml === null || $this->serverRenderedHtmlHash === null) {
            return $metadata;
        }

        $metadata['base'] = [
            'hash' => $this->serverRenderedHtmlHash,
            'bytes' => strlen($this->serverRenderedHtml),
            'revision' => $this->renderRevision,
        ];

        $minimumBytes = min(
            67108864,
            max(0, (int) config('livewire.delta.minimum_html_bytes', 8192)),
        );
        $maximumBytes = min(
            67108864,
            max($minimumBytes, (int) config('livewire.delta.maximum_html_bytes', 4194304)),
        );
        $htmlBytes = strlen($this->serverRenderedHtml);

        if ($htmlBytes < $minimumBytes || $htmlBytes > $maximumBytes) {
            return $metadata;
        }

        if (config('livewire.delta.cache_accelerator', true)) {
            $metadata['capabilities'][] = 'splice';
        }

        try {
            $blocks = app(StatelessHtmlChunks::class)->manifest(
                $this->serverRenderedHtml,
                $blockSize,
            );

            $maximumManifestBytes = min(
                65536,
                max(0, (int) config('livewire.delta.maximum_manifest_bytes', 65536)),
            );

            if (strlen($blocks) <= $maximumManifestBytes) {
                $metadata['chunks'] = [
                    'blockSize' => $blockSize,
                    'blocks' => $blocks,
                ];
                $metadata['capabilities'][] = 'chunks';
            }
        } catch (\InvalidArgumentException) {
            // The testing client mirrors the browser by omitting manifests it cannot safely bound.
        }

        try {
            $fragments = app(RenderFragmentTree::class)->manifest($this->serverRenderedHtml);

            $maximumFragments = min(
                1024,
                max(0, (int) config('livewire.delta.maximum_fragments', 1024)),
            );

            if (count($fragments['nodes'] ?? []) > $maximumFragments) {
                $fragments = null;
            }
        } catch (\InvalidArgumentException) {
            $fragments = null;
        }

        if ($fragments !== null && ($fragments['nodes'] ?? []) !== []) {
            $metadata['fragments'] = $fragments;
            $metadata['capabilities'][] = 'fragments';
        }

        return $metadata;
    }

    function getView()
    {
        return $this->view;
    }

    function getResponse()
    {
        return $this->response;
    }

    function untupleify($payload) {
        $value = Utils::isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->untupleify($child);
            }
        }

        return $value;
    }

    function getHtml($stripInitialData = false)
    {
        $html = $this->html;

        if ($stripInitialData) {
            $removeMe = (string) str($html)->betweenFirst(
                'wire:snapshot="', '"'
            );

            $html = str_replace($removeMe, '', $html);

            $removeMe = (string) str($html)->betweenFirst(
                'wire:effects="', '"'
            );

            $html = str_replace($removeMe, '', $html);
        }

        return $html;
    }
}
