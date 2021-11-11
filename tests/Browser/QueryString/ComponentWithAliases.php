<?php

namespace Tests\Browser\QueryString;

class ComponentWithAliases extends ComponentWithTraits
{
    protected $queryString = [
        'page' => ['except' => 1, 'as' => 'p'],
        'search' => ['except' => '', 'as' => 's'],
    ];
}
