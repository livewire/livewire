<?php

namespace Livewire\Mechanisms;

use Livewire\TestCase;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;

class HijackBladeTest extends TestCase
{
    /** @test */
    public function livewire_only_directives_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::directive('foo', function ($expression) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\HijackBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    /** @test */
    public function livewire_only_precompilers_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::precompiler('/@foo/sm', function ($matches) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\HijackBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }
}

class HijackBladeTestComponent extends Component
{
    public function render()
    {
        return '<div>@foo</div>';
    }
}
