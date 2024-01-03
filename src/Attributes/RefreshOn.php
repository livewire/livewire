<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportEvents\BaseRefreshOn;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class RefreshOn extends BaseRefreshOn
{
    //
}
