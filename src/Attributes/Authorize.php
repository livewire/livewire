<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportAuthorization\BaseAuthorize;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Authorize extends BaseAuthorize
{
    //
}
