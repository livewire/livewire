<?php

namespace Tests\Browser\Morphdom\Transitions;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public const DATA = [
        'John',
        'Joseph',
        'Josh',
        'Mark',
        'Mario',
        'Milton',
    ];

    public $search = 'M';

    public function getNamesProperty()
    {
        return collect(self::DATA)->filter(function ($name) {
            return strpos($name, $this->search) !== false;
        })->all();
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
