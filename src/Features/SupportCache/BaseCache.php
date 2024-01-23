<?php

namespace Livewire\Features\SupportCache;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BaseCache extends LivewireAttribute
{
    function __construct(
        protected $key = null,
        protected $ttl = null,
        protected $private = false,
    ) {}

    public function mount($params)
    {
        if (! $this->exists()) return;

        $fromCache = $this->read();

        $this->setValue($fromCache);
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
        $key = $this->key ?: (string) 'lw' . crc32($this->component->getName() . $this->getName());

        return $this->private ? Session::getId() . '-' . $key : $key;
    }
}
