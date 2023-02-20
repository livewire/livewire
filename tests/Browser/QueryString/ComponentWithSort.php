<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentWithSort extends BaseComponent
{
    public $page = 1;
    public $search = '';
    protected $queryString = [
        'page' => ['sort' => 2],
        'search' => ['sort' => 1],
    ];
    public function render()
    {
        return '<input wire:model="search" type="text" dusk="search">';
    }
}
