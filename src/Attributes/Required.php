<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportRequiredProperties\BaseRequired;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required extends BaseRequired
{
    //
}
