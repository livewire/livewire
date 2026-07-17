<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Livewire\Mechanisms\HandleRequests\SnapshotStateStore;

class ResponseTransport
{
    public function __construct(
        protected HtmlDelta $deltas,
        protected RenderStateStore $states,
        protected StatelessHtmlChunks $chunks,
        protected RenderFragmentTree $fragments,
        protected SnapshotStateStore $snapshots,
    ) {}

    public function configuration(): array
    {
        [$minimumBytes, $maximumBytes] = $this->renderByteRange();
        $maximumPortableBytes = min($maximumBytes, 67108864);

        return [
            'v' => 1,
            'minimumBytes' => min($minimumBytes, $maximumPortableBytes),
            'maximumBytes' => $maximumPortableBytes,
            'blockSize' => min(
                65536,
                max(256, (int) config('livewire.delta.block_size', 2048)),
            ),
            'maximumManifestBytes' => min(
                65536,
                max(0, (int) config('livewire.delta.maximum_manifest_bytes', 65536)),
            ),
            'maximumFragments' => min(
                1024,
                max(0, (int) config('livewire.delta.maximum_fragments', 1024)),
            ),
            'cacheAccelerator' => (bool) config('livewire.delta.cache_accelerator', true),
            'snapshotDelta' => (bool) config('livewire.delta.snapshot_delta', true),
            'snapshotReferences' => (bool) config('livewire.delta.snapshot_references', false),
            'maximumRequestBytes' => $this->maximumRequestBytes(),
        ];
    }

    public function encode(mixed $payload, array $contexts): mixed
    {
        if (config('livewire.update_engine', 'morph') !== 'delta') {
            return $payload;
        }

        if (! is_array($payload)) return $payload;

        // This descriptor is deliberately top-level so renderless responses
        // still renegotiate request transport. An explicit null tells clients
        // to clear a threshold advertised by an older server/configuration.
        $payload['transport'] = [
            'v' => 1,
            'requestGzip' => $this->requestGzipMinimumBytes(),
        ];

        if (! is_array($payload['components'] ?? null)) return $payload;

        foreach ($payload['components'] as &$response) {
            if (! is_array($response)) continue;

            $componentId = $this->componentId($response);
            $context = is_string($componentId)
                ? $this->takeContext($contexts, $componentId)
                : null;

            if (! is_array($context)) continue;

            $metadata = is_array($context['render'] ?? null) ? $context['render'] : [];

            $this->encodeSnapshot($response, $context, $metadata, $componentId);
            $this->encodeRender($response, $metadata, $componentId);
        }

        unset($response);

        return $payload;
    }

    protected function encodeRender(array &$response, array $metadata, string $componentId): void
    {
        if (! is_array($response['effects'] ?? null)) return;

        $html = $response['effects']['html'] ?? null;

        if (! is_string($html)) return;

        $targetHash = $this->deltas->hash($html);

        if (isset($metadata['htmlHash'])) {
            $this->encodeLegacyRender($response, $metadata['htmlHash'], $componentId, $html, $targetHash);
            $this->rememberHtml($componentId, $targetHash, $html);

            return;
        }

        if (($metadata['v'] ?? null) !== 1) {
            // Seed the first experimental delta client during a rolling deploy.
            // It sends no hash until it receives one alongside a full render.
            $effects = $this->cleanRenderEffects($response['effects']);
            $effects['html'] = $html;
            $effects['htmlHash'] = $targetHash;
            $response['effects'] = $effects;

            $this->rememberHtml($componentId, $targetHash, $html);

            return;
        }

        $targetBytes = strlen($html);
        $capabilities = $metadata['capabilities'] ?? [];
        $base = is_array($metadata['base'] ?? null) ? $metadata['base'] : null;
        $manifestTax = $this->requestManifestTax($metadata);
        $fullDescriptor = [
            'v' => 1,
            'mode' => 'full',
            'target' => $targetHash,
            'bytes' => $targetBytes,
        ];
        $fullEffects = $this->cleanRenderEffects($response['effects']);
        $fullEffects['html'] = $html;
        $fullEffects['render'] = $fullDescriptor;
        $selectedEffects = $fullEffects;
        $selectedTax = $manifestTax;

        if ($this->hasCapability($capabilities, 'same')
            && is_array($base)
            && hash_equals($targetHash, $base['hash'])
            && $targetBytes === $base['bytes']
        ) {
            $selectedEffects = $this->effectCandidate(
                $response['effects'],
                [
                    'v' => 1,
                    'mode' => 'same',
                    'base' => $base['hash'],
                    'target' => $targetHash,
                    'bytes' => $targetBytes,
                ],
            );
        } elseif ($this->canBuildPortableCandidates($html, $base)) {
            $candidates = $this->renderCandidates(
                $response['effects'],
                $metadata,
                $capabilities,
                $base,
                $componentId,
                $html,
                $targetHash,
                $targetBytes,
                $manifestTax,
            );

            [$selectedEffects, $selectedTax] = $this->selectRenderCandidate(
                $fullEffects,
                $candidates,
                $manifestTax,
            );
        }

        if ($minimumBytes = $this->requestGzipMinimumBytes()) {
            $fullEffects['render']['requestGzip'] = $minimumBytes;
            $selectedEffects['render']['requestGzip'] = $minimumBytes;
        }

        $selectedEffects['render']['stats'] = $this->renderStats(
            $fullEffects,
            $selectedEffects,
            $selectedTax,
        );

        $response['effects'] = $selectedEffects;

        $this->rememberHtml($componentId, $targetHash, $html);
    }

    protected function renderCandidates(
        array $effects,
        array $metadata,
        array $capabilities,
        array $base,
        string $componentId,
        string $html,
        string $targetHash,
        int $targetBytes,
        int $manifestTax,
    ): array {
        $candidates = [];
        $baseHash = $base['hash'];
        $baseBytes = $base['bytes'];

        if ($this->hasCapability($capabilities, 'splice')
            && config('livewire.delta.cache_accelerator', true)
        ) {
            $previousHtml = $this->previousHtml($componentId, $baseHash);

            if (is_string($previousHtml) && strlen($previousHtml) === $baseBytes) {
                $candidates[] = [
                    'effects' => $this->effectCandidate($effects, [
                        'v' => 1,
                        'mode' => 'splice',
                        'base' => $baseHash,
                        'target' => $targetHash,
                        'bytes' => $targetBytes,
                        'patches' => $this->deltas->encode($previousHtml, $html),
                    ]),
                    'tax' => $manifestTax,
                ];
            }
        }

        if ($this->hasCapability($capabilities, 'fragments')
            && is_array($metadata['fragments'] ?? null)
            && $this->fragmentManifestIsAllowed($metadata['fragments'])
        ) {
            try {
                $ops = $this->fragments->encode($html, $metadata['fragments']);
            } catch (\InvalidArgumentException) {
                $ops = null;
            } catch (\Throwable $e) {
                report($e);

                $ops = null;
            }

            if (is_array($ops) && $ops !== []) {
                $candidates[] = [
                    'effects' => $this->effectCandidate($effects, [
                        'v' => 1,
                        'mode' => 'fragments',
                        'base' => $baseHash,
                        'target' => $targetHash,
                        'bytes' => $targetBytes,
                        'ops' => $ops,
                    ]),
                    'tax' => $manifestTax,
                ];
            }
        }

        if ($this->hasCapability($capabilities, 'chunks')
            && is_array($metadata['chunks'] ?? null)
            && $this->chunkManifestIsAllowed($metadata['chunks'])
        ) {
            try {
                $ops = $this->chunks->encode(
                    $html,
                    $metadata['chunks']['blocks'],
                    $baseBytes,
                    $metadata['chunks']['blockSize'],
                );
            } catch (\InvalidArgumentException) {
                $ops = null;
            } catch (\Throwable $e) {
                report($e);

                $ops = null;
            }

            if (is_array($ops)) {
                $candidates[] = [
                    'effects' => $this->effectCandidate($effects, [
                        'v' => 1,
                        'mode' => 'chunks',
                        'base' => $baseHash,
                        'target' => $targetHash,
                        'bytes' => $targetBytes,
                        'ops' => $ops,
                    ]),
                    'tax' => $manifestTax,
                ];
            }
        }

        return $candidates;
    }

    protected function selectRenderCandidate(
        array $full,
        array $candidates,
        int $manifestTax,
    ): array
    {
        $selector = $this->selector();
        $selected = $full;
        $selectedTax = $manifestTax;
        $selectedScore = $this->score($selector->sizes($full));

        foreach ($candidates as $candidate) {
            $effects = $candidate['effects'];
            $tax = $candidate['tax'];

            if ($selector->select($full, [$effects], $tax) === $full) continue;

            $score = $this->score($selector->sizes($effects)) + $tax;

            if ($score >= $selectedScore) continue;

            $selected = $effects;
            $selectedTax = $tax;
            $selectedScore = $score;
        }

        return [$selected, $selectedTax];
    }

    protected function encodeLegacyRender(
        array &$response,
        string $baseHash,
        string $componentId,
        string $html,
        string $targetHash,
    ): void {
        $effects = $this->cleanRenderEffects($response['effects']);
        $effects['html'] = $html;
        $effects['htmlHash'] = $targetHash;
        $previousHtml = config('livewire.delta.cache_accelerator', true)
            ? $this->previousHtml($componentId, $baseHash)
            : null;

        if (! is_string($previousHtml)) {
            $response['effects'] = $effects;

            return;
        }

        $deltaEffects = $this->cleanRenderEffects($response['effects']);
        $deltaEffects['htmlDelta'] = [
            'base' => $baseHash,
            'patches' => $this->deltas->encode($previousHtml, $html),
        ];
        $deltaEffects['htmlHash'] = $targetHash;

        $response['effects'] = $this->selector()->select($effects, [$deltaEffects]);
    }

    protected function encodeSnapshot(
        array &$response,
        array $context,
        array $metadata,
        string $componentId,
    ): void {
        $snapshot = $response['snapshot'] ?? null;

        if (! is_string($snapshot)) return;

        $capabilities = $metadata['capabilities'] ?? [];

        if (config('livewire.delta.snapshot_delta', true)
            && $this->hasCapability($capabilities, 'snapshot-delta')
            && is_string($context['snapshot'] ?? null)
            && $this->snapshotsAreEligibleForDelta($context['snapshot'], $snapshot)
        ) {
            $previous = $context['snapshot'];
            $descriptor = [
                'v' => 1,
                'base' => $this->deltas->hash($previous),
                'target' => $this->deltas->hash($snapshot),
                'bytes' => strlen($snapshot),
                'patches' => $this->deltas->encode($previous, $snapshot),
            ];
            $full = ['snapshot' => $snapshot];
            $delta = ['snapshotDelta' => $descriptor];
            $selector = new RenderCandidateSelector(
                minimumRelativeSavings: max(0, min(0.99, (float) config('livewire.delta.minimum_savings', 0.1))),
                minimumAbsoluteSavingsBytes: 64,
                compressionAware: (bool) config('livewire.delta.compression_aware', true),
            );

            if ($selector->select($full, [$delta]) === $delta) {
                unset($response['snapshot']);
                $response['snapshotDelta'] = $descriptor;
            }
        }

        if (config('livewire.delta.snapshot_references', false)
            && $this->hasCapability($capabilities, 'snapshot-ref')
            && strlen($snapshot) >= max(
                0,
                (int) config('livewire.delta.snapshot_reference_minimum_bytes', 1024),
            )
            && strlen($snapshot) <= $this->maximumSnapshotReferenceBytes()
        ) {
            try {
                $reference = $this->snapshots->put($componentId, $snapshot);
            } catch (\Throwable $e) {
                report($e);

                $reference = null;
            }

            if (is_string($reference)) $response['snapshotRef'] = $reference;
        }
    }

    protected function effectCandidate(array $effects, array $descriptor): array
    {
        $effects = $this->cleanRenderEffects($effects);
        $effects['render'] = $descriptor;

        return $effects;
    }

    protected function cleanRenderEffects(array $effects): array
    {
        unset(
            $effects['html'],
            $effects['htmlHash'],
            $effects['htmlDelta'],
            $effects['render'],
            $effects['renderRecovery'],
        );

        return $effects;
    }

    protected function renderStats(array $full, array $selected, int $tax): array
    {
        $selector = $this->selector();
        $fullBytes = $this->score($selector->sizes($full));
        $selectedBytes = $this->score($selector->sizes($selected)) + $tax;

        return [
            'full' => $fullBytes,
            'selected' => $selectedBytes,
            'saved' => max(0, $fullBytes - $selectedBytes),
        ];
    }

    protected function selector(): RenderCandidateSelector
    {
        return new RenderCandidateSelector(
            minimumRelativeSavings: max(0, min(0.99, (float) config('livewire.delta.minimum_savings', 0.1))),
            minimumAbsoluteSavingsBytes: max(
                0,
                (int) config('livewire.delta.minimum_compressed_savings_bytes', 1024),
            ),
            compressionAware: (bool) config('livewire.delta.compression_aware', true),
        );
    }

    protected function score(array $sizes): int
    {
        return $sizes['gzip'] ?? $sizes['raw'];
    }

    protected function canBuildPortableCandidates(string $html, ?array $base): bool
    {
        if (! is_array($base)) return false;

        [$minimumBytes, $maximumBytes] = $this->renderByteRange();

        return strlen($html) >= $minimumBytes
            && strlen($html) <= $maximumBytes;
    }

    protected function snapshotsAreEligibleForDelta(string $previous, string $snapshot): bool
    {
        $maximumBytes = $this->maximumSnapshotBytes();

        return strlen($previous) <= $maximumBytes
            && strlen($snapshot) <= $maximumBytes;
    }

    protected function maximumSnapshotBytes(): int
    {
        return min(
            134217728,
            max(0, (int) config('livewire.delta.maximum_snapshot_bytes', 4194304)),
        );
    }

    protected function maximumSnapshotReferenceBytes(): int
    {
        $maximumBytes = $this->maximumSnapshotBytes();
        $maximumPayloadBytes = config('livewire.payload.max_size');

        if ($maximumPayloadBytes === null) return $maximumBytes;

        // A cache miss retries with the full snapshot JSON-encoded inside the
        // request envelope. Keep enough headroom for escaping, updates, calls,
        // render metadata, and the rest of a bundled request.
        return min($maximumBytes, intdiv(max(0, (int) $maximumPayloadBytes), 2));
    }

    protected function maximumRequestBytes(): ?int
    {
        $maximumBytes = config('livewire.payload.max_size');

        if ($maximumBytes === null) return null;

        return min(2147483647, max(0, (int) $maximumBytes));
    }

    protected function requestGzipMinimumBytes(): ?int
    {
        if (! config('livewire.delta.request_compression', false)) return null;

        return min(
            16777216,
            max(1, (int) config('livewire.delta.request_compression_minimum_bytes', 1024)),
        );
    }

    protected function requestManifestTax(array $metadata): int
    {
        $manifests = array_filter([
            'chunks' => $metadata['chunks'] ?? null,
            'fragments' => $metadata['fragments'] ?? null,
        ], fn ($manifest) => is_array($manifest));

        if ($manifests === []) return 0;

        $json = json_encode($manifests, JSON_THROW_ON_ERROR);

        if (request()->header('Content-Encoding') !== 'gzip'
            || ! function_exists('gzencode')
        ) return strlen($json);

        $compressed = gzencode($json, 1);

        return is_string($compressed) ? strlen($compressed) : strlen($json);
    }

    protected function chunkManifestIsAllowed(array $manifest): bool
    {
        $limit = max(0, (int) config('livewire.delta.maximum_manifest_bytes', 65536));

        return is_string($manifest['blocks'] ?? null)
            && strlen($manifest['blocks']) <= $limit;
    }

    protected function fragmentManifestIsAllowed(array $manifest): bool
    {
        $limit = max(0, (int) config('livewire.delta.maximum_fragments', 1024));

        return is_array($manifest['nodes'] ?? null)
            && count($manifest['nodes']) <= $limit;
    }

    protected function renderByteRange(): array
    {
        $minimumBytes = min(
            134217728,
            max(0, (int) config('livewire.delta.minimum_html_bytes', 8192)),
        );
        $maximumBytes = min(
            134217728,
            max($minimumBytes, (int) config('livewire.delta.maximum_html_bytes', 4194304)),
        );

        return [$minimumBytes, $maximumBytes];
    }

    protected function hasCapability(mixed $capabilities, string $capability): bool
    {
        return is_array($capabilities) && in_array($capability, $capabilities, true);
    }

    protected function componentId(array $response): ?string
    {
        if (is_string($response['id'] ?? null)) return $response['id'];

        if (! is_string($response['snapshot'] ?? null)) return null;

        $snapshot = json_decode($response['snapshot'], true);
        $componentId = $snapshot['memo']['id'] ?? null;

        return is_string($componentId) ? $componentId : null;
    }

    protected function previousHtml(string $componentId, string $hash): ?string
    {
        try {
            return $this->states->get($componentId, $hash);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    protected function rememberHtml(string $componentId, string $hash, string $html): void
    {
        if (! config('livewire.delta.cache_accelerator', true)) return;

        [$minimumBytes, $maximumBytes] = $this->renderByteRange();

        if (strlen($html) < $minimumBytes || strlen($html) > $maximumBytes) return;

        try {
            $this->states->put($componentId, $hash, $html);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function takeContext(array &$contexts, string $componentId): ?array
    {
        $context = $contexts[$componentId] ?? null;

        if (! is_array($context)) return null;

        // Multiple bundled payloads with the same component id are unusual,
        // but keeping a queue preserves each response's immutable baseline.
        if (array_is_list($context)) {
            $next = array_shift($contexts[$componentId]);

            return is_array($next) ? $next : null;
        }

        unset($contexts[$componentId]);

        return $context;
    }
}
