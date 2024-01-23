<?php

namespace Livewire\Features\SupportSession;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Attribute;
use Illuminate\Support\Facades\Cache;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BaseCache extends LivewireAttribute
{
    function __construct(
        protected $key = null,
        protected $ttl = null,
    ) {}

    public function mount($params)
    {
        if (! $this->exists()) return;

        $fromSession = $this->read();

        $this->setValue($fromSession);
    }

    public function dehydrate($context)
    {
        $this->write();
    }

    protected function exists()
    {
        return Cache::has($this->key());
    }

    protected function read()
    {
        return Cache::get($this->key());
    }

    protected function write()
    {
        Cache::put($this->key(), $this->getValue(), $this->ttl);
    }

    protected function key()
    {
        return $this->key ?: (string) 'lw' . crc32($this->component->getName() . $this->getName());
    }
}
