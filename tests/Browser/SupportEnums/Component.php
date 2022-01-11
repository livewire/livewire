<?php

namespace Tests\Browser\SupportEnums;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component as BaseComponent;

enum EnumTest: string
{
    case TEST = 'Be excellent to each other';
}

class Component extends BaseComponent
{
    public EnumTest $enum;

    public function mount()
    {
        $this->enum = EnumTest::TEST;
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
