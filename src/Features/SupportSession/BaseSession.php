<?php

namespace Livewire\Features\SupportSession;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\HandleSynths\CorruptPersistedValueException;
use Livewire\Mechanisms\HandleSynths\PersistedValueCodec;
use Illuminate\Support\Facades\Session;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BaseSession extends LivewireAttribute
{
    protected $codec;

    function __construct(
        protected $key = null,
    ) {}

    public function mount($params)
    {
        if (! $this->exists()) return;

        try {
            $fromSession = $this->read();
        } catch (CorruptPersistedValueException) {
            Session::forget($this->key());

            return;
        }

        // A stored value can become unassignable after it was persisted: its
        // type no longer matches the property (\TypeError), or it's a scalar
        // backing a since-removed enum case (\ValueError from Enum::from()).
        // Either way the stale value is forgotten and the default preserved.
        try {
            $this->setValue($fromSession);
        } catch (\TypeError|\ValueError) {
            Session::forget($this->key());
        }
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
        // Always decode so changing session serializers doesn't strand values
        // that Livewire encoded before the configuration changed.
        $key = $this->key();

        return $this->codec()->decodeFromStorage(
            Session::get($key),
            $this->component,
            $this->getName(),
            $key,
        );
    }

    protected function write()
    {
        $value = $this->getValue();
        $key = $this->key();

        if ($this->sessionIsJsonSerialized()) {
            $value = $this->codec()->encodeForStorage(
                $value,
                $this->component,
                $this->getName(),
                $key,
            );
        }

        Session::put($key, $value);
    }

    protected function sessionIsJsonSerialized()
    {
        return config('session.serialization', 'php') === 'json';
    }

    protected function codec()
    {
        return $this->codec ??= app(PersistedValueCodec::class);
    }

    protected function key()
    {
        if (! $this->key) {
            return (string) 'lw' . crc32($this->component->getName() . $this->getName());
        }

        return self::replaceDynamicPlaceholders($this->key, $this->component);
    }

    static function replaceDynamicPlaceholders($key, $component)
    {
        return preg_replace_callback('/\{(.*)\}/U', function ($matches) use ($component) {
            return data_get($component, $matches[1], function () use ($matches) {
                throw new \Exception('Unable to evaluate dynamic session key placeholder: '.$matches[0]);
            });
        }, $key);
    }
}
