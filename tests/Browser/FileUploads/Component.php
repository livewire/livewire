<?php

namespace Tests\Browser\FileUploads;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Stringable;
use Livewire\Component as BaseComponent;
use Livewire\WithFileUploads;

class Component extends BaseComponent
{
    use WithFileUploads;

    public $foo;

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
