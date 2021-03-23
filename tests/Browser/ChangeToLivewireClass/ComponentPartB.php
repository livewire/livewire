<?php

namespace Tests\Browser\ChangeToLivewireClass;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentPartB extends BaseComponent
{
//    /** @var string */
//    public $aProp = 'A';

    /** @var string */
    public $zProp = 'Z';

    /** @var integer */
    public $count = 1;

    public function incCount(): void
    {
        $this->count++;
    }

    public function render()
    {
        return View::file(__DIR__ . '/view.blade.php');
    }
}
