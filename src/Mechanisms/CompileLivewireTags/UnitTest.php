<?php

namespace Livewire\Mechanisms\CompileLivewireTags;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class UnitTest extends \Tests\TestCase
{
    #[Test]
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

