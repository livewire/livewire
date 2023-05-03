<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\WithFileUploads;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_upload_a_file()
    {
        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function save()
            {
                dd($this->photo);
            }

            function render() { return <<<HTML
            <div>
                <input type="file" wire:model="photo">

                <button wire:click="save">Save</button>
            </div>
            HTML; }
        })
        ->tinker()
        ;
    }
}
