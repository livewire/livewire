<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Livewire;
use Livewire\Component;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Blade;

class Test extends \Tests\TestCase
{
    /** @test */
    public function conditional_markers_are_only_added_to_if_statements_wrapping_elements()
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public function render() {
                return '<div>@if (true) <div @if (true) @endif></div> @endif</div>';
            }
        });

        $output = Blade::render('
            <div>@if (true) <div></div> @endif</div>
            <livewire:foo />
        ');

        $this->assertCount(2, explode('__BLOCK__', $output));
        $this->assertCount(2, explode('__ENDBLOCK__', $output));
    }
}

