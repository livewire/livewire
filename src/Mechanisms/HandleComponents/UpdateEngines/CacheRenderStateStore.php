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
        $key = $this->key($componentId);
        $state = $repository->get($key);

        if (! is_array($state)
            || ! is_string($state['hash'] ?? null)
            || ! is_string($state['html'] ?? null)
            || ! hash_equals($state['hash'], $hash)
        ) {
            return null;
        }

        if (! hash_equals($hash, $this->deltas->hash($state['html']))) {
            $repository->forget($key);

            return null;
        }

        return $state['html'];
    }

    public function put(string $componentId, string $hash, string $html): void
    {
        $this->repository()->put(
            $this->key($componentId),
            ['hash' => $hash, 'html' => $html],
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

    protected function key(string $componentId): string
    {
        return "livewire:delta:{$componentId}";
    }
}
