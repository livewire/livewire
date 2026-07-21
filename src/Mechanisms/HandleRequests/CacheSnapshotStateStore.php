<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;

class CacheSnapshotStateStore implements SnapshotStateStore
{
    protected const MAX_SNAPSHOT_BYTES = 134217728;

    public function __construct(protected CacheFactory $cache) {}

    public function get(string $reference, string $componentId): ?string
    {
        if (! preg_match('/\A[A-Za-z0-9_-]{24}\z/', $reference)) return null;

        $repository = $this->repository();
        $key = $this->key($reference);
        $state = $repository->get($key);

        if ($state === null) return null;

        if (! is_array($state)
            || ! is_string($state['id'] ?? null)
            || ! is_string($state['hash'] ?? null)
        ) {
            $repository->forget($key);

            return null;
        }

        // A reference is scoped to its component, but a mismatched claimant
        // must not be able to evict otherwise valid state.
        if (! hash_equals($state['id'], $componentId)) return null;

        // Accept references minted immediately before a rolling deployment of
        // the compressed store format.
        if (is_string($state['snapshot'] ?? null)) {
            $snapshot = $state['snapshot'];
        } elseif (is_int($state['bytes'] ?? null)
            && $state['bytes'] >= 0
            && $state['bytes'] <= $this->maximumBytes()
            && is_string($state['payload'] ?? null)
            && is_string($state['encoding'] ?? null)
        ) {
            $snapshot = $this->decode(
                $state['payload'],
                $state['encoding'],
                $state['bytes'],
            );
        } else {
            $snapshot = null;
        }

        if (! is_string($snapshot)
            || strlen($snapshot) > $this->maximumBytes()
            || (isset($state['bytes']) && $state['bytes'] !== strlen($snapshot))
            || ! hash_equals($state['hash'], hash('sha256', $snapshot))
        ) {
            $repository->forget($key);

            return null;
        }

        return $snapshot;
    }

    public function put(string $componentId, string $snapshot): ?string
    {
        $bytes = strlen($snapshot);

        if ($bytes > $this->maximumBytes()) return null;

        $reference = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        [$payload, $encoding] = $this->encode($snapshot);

        $stored = $this->repository()->put(
            $this->key($reference),
            [
                'id' => $componentId,
                'hash' => hash('sha256', $snapshot),
                'bytes' => $bytes,
                'encoding' => $encoding,
                'payload' => $payload,
            ],
            max(1, (int) config('livewire.delta.snapshot_reference_ttl', 300)),
        );

        return $stored === false ? null : $reference;
    }

    protected function repository(): Repository
    {
        $store = config('livewire.delta.snapshot_store')
            ?: config('livewire.delta.store');

        return $store
            ? $this->cache->store($store)
            : $this->cache->store();
    }

    protected function key(string $reference): string
    {
        return "livewire:snapshot:{$reference}";
    }

    protected function encode(string $snapshot): array
    {
        if (! function_exists('gzencode')) return [$snapshot, 'identity'];

        $compressed = gzencode($snapshot, 1);

        if (! is_string($compressed) || strlen($compressed) >= strlen($snapshot)) {
            return [$snapshot, 'identity'];
        }

        return [$compressed, 'gzip'];
    }

    protected function decode(string $payload, string $encoding, int $bytes): ?string
    {
        if ($encoding === 'identity') return $payload;

        if ($encoding !== 'gzip' || ! function_exists('gzdecode')) return null;

        $snapshot = @gzdecode($payload, $bytes + 1);

        return is_string($snapshot) ? $snapshot : null;
    }

    protected function maximumBytes(): int
    {
        return min(
            self::MAX_SNAPSHOT_BYTES,
            max(0, (int) config('livewire.delta.maximum_snapshot_bytes', 4194304)),
        );
    }
}
