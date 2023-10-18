<?php

namespace Livewire\Features\SupportIsolatedRequests;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BaseIsolate extends LivewireAttribute
{
}
