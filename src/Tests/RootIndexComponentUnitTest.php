<?php

namespace Livewire\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Tests\RootIndex\Index;

class RootIndexComponentUnitTest extends \Tests\TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        // Mirror an app that has `app/Livewire/Index.php` — an `Index` class sitting at the
        // very root of the configured class namespace. This has to be set before the
        // service provider boots, since that's where the location is registered...
        $app['config']->set('livewire.class_namespace', 'Livewire\\Tests\\RootIndex');
    }

    public function test_root_index_class_gets_a_usable_name()
    {
        $this->assertEquals('index', Livewire::new(Index::class)->getName());
    }

    public function test_root_index_component_renders_as_a_page_component()
    {
        Route::get('/root-index-page', Index::class);

        $this->get('/root-index-page')
            ->assertOk()
            ->assertSee('Count: 1');
    }

    public function test_root_index_component_renders_by_name()
    {
        Route::get('/root-index', fn () => Blade::render('<livewire:index />'));

        $this->get('/root-index')
            ->assertOk()
            ->assertSee('Count: 1');
    }

    public function test_root_index_component_handles_a_subsequent_update_request()
    {
        Livewire::test(Index::class)
            ->assertSee('Count: 1')
            ->call('increment')
            ->assertSee('Count: 2');
    }
}
