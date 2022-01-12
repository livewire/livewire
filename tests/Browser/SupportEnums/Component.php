<?php

namespace Tests\Browser\SupportEnums;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $enum;

    public function mount()
    {
        $this->enum = \Tests\TestEnum::TEST;
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
