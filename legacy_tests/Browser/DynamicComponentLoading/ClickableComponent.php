<?php

namespace LegacyTests\Browser\DynamicComponentLoading;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ClickableComponent extends BaseComponent
{
    public $success = false;

    public function clickMe()
    {
        // Calling this method should never fail, even if the component is loaded dynamically via POST.
        // By setting the component property here, we can draw the final message for the test to succeed.

        $this->success = true;
    }

    public function render()
    {
        return View::file(__DIR__.'/view-clickable-component.blade.php');
    }
}
