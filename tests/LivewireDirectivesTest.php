<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

class LivewireDirectivesTest extends TestCase
{
    /** @test */
    public function component_is_loaded_with_blade_directive()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $output = view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertStringContainsString('div', $output);
    }

    /** @test */
    public function can_assert_see_livewire_on_standard_blade_view()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $testResponse = new TestResponse(new class {
            public function getContent() {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        });

        $testResponse->assertSeeLivewire('foo');
    }

    /** @test */
    public function component_is_loaded_with_blade_directive_by_classname()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $output = view('render-component', [
            'component' => \App\Http\Livewire\Foo::class,
        ])->render();

        $this->assertStringContainsString('div', $output);
    }

    /** @test */
    public function this_directive_returns_javascript_component_object_string()
    {
        Livewire::test(ComponentForTestingDirectives::class)
            ->assertDontSee('@this')
            ->assertSee('window.livewire.find(');
    }

    /** @test */
    public function this_directive_isnt_registered_outside_of_livewire_component()
    {
        $output = view('this-directive')->render();

        $this->assertStringContainsString('@this', $output);
    }
}

class ComponentForTestingDirectives extends Component
{
    public function render()
    {
        return view('this-directive');
    }
}
