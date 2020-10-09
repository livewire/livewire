<?php

namespace Tests\Browser\ValidationErrors;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = '';

    protected $rules = [
        'foo' => 'required'
    ];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
