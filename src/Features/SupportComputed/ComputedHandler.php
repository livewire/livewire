<?php

namespace Livewire\Features\SupportComputed;

use Illuminate\Support\Facades\Cache;

abstract class ComputedHandler
{
    public $seconds;
    public $tags;

    public function __construct(
        public BaseComputed $computed,
    ) {
        $this->seconds ??= $computed->seconds;
        $this->tags ??= $computed->tags;
    }

    abstract protected function generateKey();

    public function handleGet()
    {
        $key = $this->computed->key ?: $this->generateKey();

        $closure = fn () => $this->computed->evaluateComputed();

        return match(Cache::supportsTags() && !empty($this->tags)) {
            true => Cache::tags($this->tags)->remember($key, $this->seconds, $closure),
            default => Cache::remember($key, $this->seconds, $closure)
        };
    }

    public function handleUnset()
    {
        $key = $this->computed->key ?: $this->generateKey();

        Cache::forget($key);
    }

    public function replaceDynamicPlaceholders($key)
    {
        return preg_replace_callback('/\{(.*)\}/U', function ($matches) {
            return data_get($this->computed->getComponent(), $matches[1], function () use ($matches) {
                throw new \Exception('Unable to evaluate dynamic cache key placeholder: '.$matches[0]);
            });
        }, $key);
    }
}
