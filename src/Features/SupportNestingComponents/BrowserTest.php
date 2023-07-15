<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook() {
        return function() {
            Livewire::component('page', Page::class);
            Livewire::component('first-component', FirstComponent::class);
            Livewire::component('second-component', SecondComponent::class);
            Livewire::component('third-component', ThirdComponent::class);

            Route::get('/', Page::class)->middleware('web');
        };
    }

    /** @test */
    function can_add_new_components()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('Page')
                ->click('@add-first')
                ->waitForText('First Component Rendered')
                ->click('@add-second')
                ->waitForText('Second Component Rendered')
                ->click('@add-third')
                ->waitForText('Third Component Rendered')
                ->click('@remove-second')
                ->waitUntilMissingText('Second Component Rendered')
                ->assertSee('First Component Rendered')
                ->assertSee('Third Component Rendered');
        });
    }
}

class Page extends Component
{
    public $components = [];

    public function add($item)
    {
        $this->components[$item] = [];
    }

    public function remove($item)
    {
        unset($this->components[$item]);
    }

    public function render()
    {
        return <<<HTML
        <div>
            <div>Page</div>

            @foreach(\$components as \$component => \$params)
                @livewire(\$component, \$params, key(\$component))
            @endforeach

            <div>
                <button dusk="add-first" wire:click="add('first-component')">Add first-component</button>
                <button dusk="add-second" wire:click="add('second-component')">Add second-component</button>
                <button dusk="add-third" wire:click="add('third-component')">Add third-component</button>
            </div>

            <div>
                <button dusk="remove-first" wire:click="remove('first-component')">Remove first-component</button>
                <button dusk="remove-second" wire:click="remove('second-component')">Remove second-component</button>
                <button dusk="remove-third" wire:click="remove('third-component')">Remove third-component</button>
            </div>
        </div>
        HTML;
    }
}

class FirstComponent extends Component {
    function render() {
        return '<div>First Component Rendered</div>';
    }
}

class SecondComponent extends Component {
    function render() {
        return '<div>Second Component Rendered</div>';
    }
}

class ThirdComponent extends Component {
    function render() {
        return '<div>Third Component Rendered</div>';
    }
}
