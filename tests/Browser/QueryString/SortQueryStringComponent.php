<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class SortQueryStringComponent extends BaseComponent
{
    public $foo = 'bar';
    public $bar = 'baz';

    protected $queryString = [
        'foo',
        'bar',
    ];

    public function mount()
    {
        config()->set('livewire.force_querystring_sort', true);
    }

    public function render()
    {
        return <<< 'HTML'
<div></div>
HTML;
    }
}
