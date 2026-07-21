<?php

namespace Livewire\Mechanisms\HandleComponents\UpdateEngines;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;

class CacheRenderStateStore implements RenderStateStore
{
    public function __construct(
        protected CacheFactory $cache,
        protected HtmlDelta $deltas,
    ) {}

    public function get(string $componentId, string $hash): ?string
    {
        $repository = $this->repository();
        $key = $this->key($componentId, $hash);
        $state = $repository->get($key);

        if ($state === null) return null;

        if (! is_array($state)
            || ! is_string($state['hash'] ?? null)
            || ! is_int($state['bytes'] ?? null)
            || $state['bytes'] < 0
            || $state['bytes'] > $this->maximumBytes()
            || ! is_string($state['payload'] ?? null)
            || ! is_string($state['encoding'] ?? null)
            || ! hash_equals($state['hash'], $hash)
        ) {
            $repository->forget($key);

            return null;
        }

        $html = $this->decode($state['payload'], $state['encoding'], $state['bytes']);

        if ($html === null
            || $state['bytes'] !== strlen($html)
            || ! hash_equals($hash, $this->deltas->hash($html))
        ) {
            $repository->forget($key);

            return null;
        }

        return $html;
    }

    public function put(string $componentId, string $hash, string $html): void
    {
        if (strlen($html) > $this->maximumBytes()
            || ! hash_equals($hash, $this->deltas->hash($html))
        ) {
            throw new \InvalidArgumentException('Rendered HTML does not match its cache descriptor.');
        }

        [$payload, $encoding] = $this->encode($html);

        $this->repository()->put(
            $this->key($componentId, $hash),
            [
                'hash' => $hash,
                'bytes' => strlen($html),
                'encoding' => $encoding,
                'payload' => $payload,
            ],
            max(1, (int) config('livewire.delta.ttl', 300)),
        );
    }

    protected function repository(): Repository
    {
        $store = config('livewire.delta.store');

        return $store
            ? $this->cache->store($store)
            : $this->cache->store();
    }

    protected function key(string $componentId, string $hash): string
    {
        return 'livewire:render:'.hash('sha256', $componentId."\0".$hash);
    }

    protected function encode(string $html): array
    {
        if (! function_exists('gzencode')) return [$html, 'identity'];

        $compressed = gzencode($html, 1);

        if (! is_string($compressed) || strlen($compressed) >= strlen($html)) {
            return [$html, 'identity'];
        }

        return [$compressed, 'gzip'];
    }

    protected function decode(string $payload, string $encoding, int $bytes): ?string
    {
        if ($encoding === 'identity') return $payload;

        if ($encoding !== 'gzip' || ! function_exists('gzdecode')) return null;

        $html = @gzdecode($payload, $bytes + 1);

        return is_string($html) ? $html : null;
    }

    protected function maximumBytes(): int
    {
        $minimumBytes = max(0, (int) config('livewire.delta.minimum_html_bytes', 8192));

        return min(
            134217728,
            max($minimumBytes, (int) config('livewire.delta.maximum_html_bytes', 4194304)),
        );
    }
}
