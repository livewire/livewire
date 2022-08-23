<?php

namespace Livewire\Mechanisms;

use Livewire\TestCase;
use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;

class CompileLivewireTagsTest extends TestCase
{
    /** @test */
    public function can_compile_livewire_self_closing_tags()
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public function render() {
                return '<div>noop</div>';
            }
        });

        $output = Blade::render('
            <livewire:foo />
        ');

        $this->assertStringNotContainsString('<livewire:', $output);
    }
}

