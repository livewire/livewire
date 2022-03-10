<?php

namespace Tests\Browser\EventListeners;

use Illuminate\Support\Facades\View;

class ChildComponent extends TestComponent
{
    public function render()
    {
        return View::file(__DIR__ . '/child-view.blade.php');
    }
}
