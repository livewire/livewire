<?php

namespace LegacyTests\Browser\QueryString;

use Livewire\Component as BaseComponent;

class ComponentWithMethodInsteadOfProperty extends BaseComponent
{
    public $foo = 'bar';

    public function queryString()
    {
        return ['foo' => ['alwaysShow' => true]];
    }

    public function render()
    {
        return '<div>{{ $foo }}</div>';
    }
}
