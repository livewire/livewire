<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportCookie\BaseCookie;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cookie extends BaseCookie
{
    //
}
