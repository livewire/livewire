<?php

namespace Livewire\Features\SupportTesting\Tests;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertViewIsUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_view_is()
    {
        Livewire::test(ViewComponent::class)
            ->assertViewIs('null-view');
    }

    /** @test */
    function can_assert_view_is_for_components_without_render_method()
    {
        File::ensureDirectoryExists($this->livewireViewsPath());
        File::put($this->livewireViewsPath('foo.blade.php'), '<div></div>');

        Livewire::test(ComponentWithoutRenderMethod::class)
            ->assertViewIs('livewire.foo');
    }

    /** @test */
    function can_assert_view_is_for_components_without_render_method_in_subfolder()
    {
        File::ensureDirectoryExists($this->livewireViewsPath('foo'));
        File::put($this->livewireViewsPath('foo/bar.blade.php'), '<div></div>');

        Livewire::test(ComponentWithoutRenderMethodInSubfolder::class)
            ->assertViewIs('livewire.foo.bar');
    }

    /** @test */
    function can_assert_view_is_for_components_without_render_method_with_custom_view_path()
    {
        config()->set('livewire.view_path', $this->livewireViewsPath('foo'));

        File::ensureDirectoryExists($this->livewireViewsPath('foo'));
        File::put($this->livewireViewsPath('foo/bar.blade.php'), '<div></div>');

        Livewire::test(ComponentWithoutRenderMethodWithCustomViewPath::class)
            ->assertViewIs('livewire.foo.bar');
    }
}

class ViewComponent extends Component
{
    function render()
    {
        return view('null-view');
    }
}

class ComponentWithoutRenderMethod extends Component
{
    public function getName()
    {
        return 'foo';
    }
}

class ComponentWithoutRenderMethodInSubfolder extends Component
{
    public function getName()
    {
        return 'foo.bar';
    }
}

class ComponentWithoutRenderMethodWithCustomViewPath extends Component
{
    public function getName()
    {
        return 'bar';
    }
}
