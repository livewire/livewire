<?php

namespace Livewire\Mechanisms\CompileLivewireTags;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_can_compile_livewire_self_closing_tags()
    {
        Livewire::component('foo', new class extends Component {
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

