<?php

namespace Livewire\Features\SupportValidation;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class BaseRule extends BaseValidate
{
   // This class is kept here for backwards compatibility...
}
