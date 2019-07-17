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
    function test_assert_set()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo')
            ->assertSet('name', 'foo');
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
