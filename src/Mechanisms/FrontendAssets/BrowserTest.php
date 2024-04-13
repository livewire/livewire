<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class BrowserTest extends \Tests\BrowserTestCase
{
    #[Test]
    public function can_register_a_custom_javascript_endpoint()
    {
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/custom/livewire.js', function () use ($handle) {
                return "alert('hi mom')";
            });
        });

        Livewire::visit(new class extends \Livewire\Component {
            function render() { return '<div></div>'; }
        })
        ->assertDialogOpened('hi mom')
        ;
    }
}
