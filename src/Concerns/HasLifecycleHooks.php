<?php

namespace Livewire\Concerns;

use BadMethodCallException;

trait HasLifecycleHooks
{
    // This is bad, will fix - but stupid PHP strict mode throwing "declaration not compatible" crap
    public function __call($method, $params)
    {
        if ($method === 'created') {
            return;
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }

    public function mounted()
    {
        //
    }

    public function beforeUpdate()
    {
        //
    }

    public function updated()
    {
        //
    }
}
