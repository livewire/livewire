<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Str;

class BrowserTests extends \Tests\BrowserTestCase
{
    /** @test */
    public function input_is_reseted()
    {
        Livewire::visit(Page::class)
            ->value('input[id="title"]', 'This is a title')
            ->assertInputValue('#title', 'This is a title')
            ->waitForLivewire()
            ->assertInputValue('#title', 'This Is A Title');
    }
}

class Page extends Component
{
    public $title = '';

    public function updatedTitle($value)
    {
        $this->title = Str::headline($value);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <input dusk="title" type="text" id="title" wire:model.live="title" />

            <div>
                <button dusk="save" wire:click="save">Save</button>
            </div>
        </div>
        HTML;
    }
}

