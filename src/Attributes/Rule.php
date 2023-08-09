<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportValidation\Rule as BaseRule;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class Rule extends BaseRule
{
    //
}

