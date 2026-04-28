<?php

namespace Livewire\Features\SupportSession;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
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

        // Hydrate via Livewire's synth pipeline so non-primitive types
        // (Collection, Carbon, models) survive JSON-serialised sessions...
        return app(HandleComponents::class)->hydrate($value, new ComponentContext($this->component), '');
    }

    protected function write()
    {
        // Dehydrate to a JSON-safe tuple so the original type is restored
        // on read regardless of the session driver's serialisation format...
        $value = app(HandleComponents::class)->dehydrate($this->getValue(), new ComponentContext($this->component), '');

        Session::put($this->key(), $value);
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
