<?php

namespace Tests\Browser\SupportEnums;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Tests\TestEnum;

class Component extends BaseComponent
{
    public TestEnum $enum;

    public function mount()
    {
        $this->enum = TestEnum::TEST;
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
