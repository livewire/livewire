<?php

namespace LegacyTests\Browser\QueryString;

class ComponentWithAliases extends ComponentWithTraits
{
    protected $queryString = [
        'search' => ['as' => 's'],
    ];
}
