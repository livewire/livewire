<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestComponent;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_register_a_custom_javascript_endpoint()
    {
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/custom/livewire.js', function () use ($handle) {
                return "alert('hi mom')";
            });
        });

        Livewire::visit(new class extends TestComponent {})
            ->assertDialogOpened('hi mom')
        ;
    }
}
