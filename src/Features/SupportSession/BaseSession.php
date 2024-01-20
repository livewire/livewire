<?php

namespace Livewire\Features\SupportSession;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Illuminate\Support\Facades\Session;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BaseSession extends LivewireAttribute
{
    function __construct(
        protected $key = null,
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
        return Session::exists($this->key());
    }

    protected function read()
    {
        return Session::get($this->key());
    }

    protected function write()
    {
        Session::put($this->key(), $this->getValue());
    }

    protected function key()
    {
        return $this->key ?: (string) 'lw' . crc32($this->component->getName() . $this->getName());
    }
}
