<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Livewire\DuskTestCase;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class SupportMorphAwareIfStatementTest extends DuskTestCase
{
    /** @test */
    public function test()
    {
        $component = new class extends Component {

        };

        $this->browse(function ($browser) {
            Livewire::visit($browser, Something::class)
                ->waitForText('foo')
                ->assertSee('foo')
            ;
        });
    }
}

class Something extends Component {
    function render() {
        return '<div>foo</div>';
    }
}
