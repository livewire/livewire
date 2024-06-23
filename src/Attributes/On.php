<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportEvents\BaseOn;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class On extends BaseOn
{
    //
}
