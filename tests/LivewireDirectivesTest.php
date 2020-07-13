<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Testing\TestResponse as Laravel7TestResponse;
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

        $fakeClass = new class {
            public function getContent()
            {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        };

        if (Application::VERSION === '7.x-dev' || version_compare(Application::VERSION, '7.0', '>=')) {
            $testResponse = new Laravel7TestResponse($fakeClass);
        } else {
            $testResponse = new TestResponse($fakeClass);
        }

        $testResponse->assertSeeLivewire('foo');
    }

    /** @test */
    public function can_assert_dont_see_livewire_on_standard_blade_view()
    {
        $fakeClass = new class {
            public function getContent()
            {
                return view('null-view')->render();
            }
        };

        if (Application::VERSION === '7.x-dev' || version_compare(Application::VERSION, '7.0', '>=')) {
            $testResponse = new Laravel7TestResponse($fakeClass);
        } else {
            $testResponse = new TestResponse($fakeClass);
        }

        $testResponse->assertDontSeeLivewire('foo');
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
    public function this_directive_can_be_used_in_nested_blade_component()
    {
        Livewire::test(ComponentForTestingNestedThisDirective::class)
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

class ComponentForTestingNestedThisDirective extends Component
{
    public function render()
    {
        return view('nested-this-directive');
    }
}
