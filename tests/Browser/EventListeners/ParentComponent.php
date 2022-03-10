<?php

namespace Tests\Browser\EventListeners;

use Illuminate\Support\Facades\View;

class ParentComponent extends TestComponent
{
    public function render()
    {
        return View::file(__DIR__ . '/parent-view.blade.php');
    }
}
