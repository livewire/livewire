<?php

namespace Tests\Browser\ChangeToLivewireClass;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentPartA extends BaseComponent
{
    /** @var integer */
    public $count = 1;

    /** @var string */
    public $aProp = 'A';

//    /** @var string */
//    public $zProp = 'Z';

    public function incCount(): void
    {
        $this->count++;
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
