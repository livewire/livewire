<?php

namespace Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportActionMiddleware\BaseMiddleware;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Middleware extends BaseMiddleware
{
    //
}