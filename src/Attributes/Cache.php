<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportCache\BaseCache;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cache extends BaseCache
{
    //
}
