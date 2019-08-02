<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\LivewireManager;

class LivewireTestingTest extends TestCase
{
    /** @test */
    function test_method_accepts_arguments_to_pass_to_mount()
    {
        $component = app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo');

        $this->assertContains('foo', $component->dom);
    }

    /** @test */
    function test_set_multiple_with_array()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo')
            ->set(['name' => 'bar'])
            ->assertSet('name', 'bar');
    }

    /** @test */
    function test_assert_set()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo')
            ->assertSet('name', 'foo');
    }

    /** @test */
    function test_assert_see()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'should see me')
            ->assertSee('should see me');
    }

    /** @test */
    function test_assert_see_doesnt_include_json_encoded_data_put_in_wire_data_attribute()
    {
        // See for more info: https://github.com/calebporzio/livewire/issues/62
        app(LivewireManager::class)
            ->test(HasMountArgumentsButDoesntPassThemToBladeView::class, 'shouldnt see me')
            ->assertDontSee('shouldnt see me');
    }

    /** @test */
    public function test_filter_middlewares()
    {
        Artisan::call('make:livewire foo');
        $manager = \Mockery::mock(LivewireManager::class)->makePartial();
        $manager->shouldReceive('currentMiddlewareStack')->andReturn(['MiddlewareA', 'MiddlewareB', 'MiddlewareC']);

        $manager->filterMiddleware(function($middleware) {
            return $middleware != 'MiddlewareB';
        });

        $this->assertEquals([
            0 => 'MiddlewareA',
            2 => 'MiddlewareC',
        ], decrypt($manager->mount('foo')->middleware));
    }
}

class HasMountArguments extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('show-name');
    }
}

class HasMountArgumentsButDoesntPassThemToBladeView extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
