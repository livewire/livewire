<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class FullPageComponentConfigurableLayoutTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::component("fullpage-test", FullPageComponentForConfigurableLayoutTest::class);

        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        config()->set("livewire.fullpage_layout", "layouts.app");

        parent::tearDown();
    }

    /** @test */
    public function uses_standard_app_layout_by_default()
    {
        Route::get('/configurable-layout-test', FullPageComponentForConfigurableLayoutTest::class);

        $this
            ->get('/configurable-layout-test')
            ->assertSee('bar')
            ->assertDontSee('baz');
    }

    /** @test */
    public function can_configure_a_default_layout()
    {
        Route::get('/configurable-layout-test', FullPageComponentForConfigurableLayoutTest::class);

        config()->set("livewire.fullpage_layout", "layouts.app-with-baz-hardcoded");

        $this
            ->get('/configurable-layout-test')
            ->assertSee('bar')
            ->assertSee('baz');
    }
}

class FullPageComponentForConfigurableLayoutTest extends Component
{
    public $name = 'bar';

    public function render()
    {
        return view('show-name');
    }
}
