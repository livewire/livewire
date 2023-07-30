<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function input_is_reseted()
    {
        Livewire::visit(Page::class)
            ->assertSee('Page')
            ->assertValue('input[id="title"]', 'This is a title')

            ->waitForLivewire()->click('@save')
            ->assertInputValue('#title', '');
    }
}

class Page extends Component
{
    public $title = 'This is a title';

    public function save()
    {
        $this->reset('title');
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Page</div>

            <input dusk="title" type="text" id="title" wire:model="title" />

            <div>
                <button dusk="save" wire:click="save">Save</button>
            </div>
        </div>
        HTML;
    }
}

