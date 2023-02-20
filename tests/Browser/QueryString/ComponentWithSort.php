<?php

namespace QueryString;

use Tests\Browser\QueryString\ComponentWithTraits;

class ComponentWithSort extends ComponentWithTraits
{
    protected $queryString = [
        'page' => ['sort' => 2],
        'search' => ['sort' => 1],
    ];
}
