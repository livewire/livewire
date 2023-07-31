<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportEvents\On as BaseOn;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class On extends BaseOn
{
    //
}
