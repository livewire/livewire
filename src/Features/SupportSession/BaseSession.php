<?php

namespace Livewire\Features\SupportSession;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleSynths\HandleSynths;
use Livewire\Drawer\Utils;
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
        $value = Session::get($this->key());

        // If a synth tuple was stored (because the session is JSON serialized),
        // hydrate it back into its original type (Collection, Carbon, etc.)...
        if (Utils::isSyntheticTuple($value)) {
            $value = app(HandleSynths::class)->hydrate($value, new ComponentContext($this->component), $this->getName());
        }

        return $value;
    }

    protected function write()
    {
        $value = $this->getValue();

        // JSON serialized sessions can't store PHP objects, so dehydrate the
        // value into a JSON-safe synth tuple that `read()` can restore later.
        // PHP serialized sessions store objects natively, so skip the overhead...
        if ($this->sessionIsJsonSerialized() && ! Utils::isAPrimitive($value)) {
            $value = app(HandleSynths::class)->dehydrate($value, new ComponentContext($this->component), $this->getName());
        }

        Session::put($this->key(), $value);
    }

    protected function sessionIsJsonSerialized()
    {
        return config('session.serialization', 'php') === 'json';
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
