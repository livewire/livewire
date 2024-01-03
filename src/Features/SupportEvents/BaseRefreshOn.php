<?php

namespace Livewire\Features\SupportEvents;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BaseRefreshOn extends LivewireAttribute
{
    public function __construct(public $event) {}

    public function boot()
    {
        store($this->component)->push(
            'listenersFromClassAttributes',
            '$refresh',
            $this->event,
        );
    }
}
