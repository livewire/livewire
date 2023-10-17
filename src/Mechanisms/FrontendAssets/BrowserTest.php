<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_register_a_custom_javascript_endpoint()
    {
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/custom/livewire.js', function () {
                return "alert('hi mom')";
            });
        });

        Livewire::visit(new class extends \Livewire\Component
        {
            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertDialogOpened('hi mom');
    }
}
