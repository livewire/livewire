<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

use function Livewire\store;
use function Livewire\str;

class ComponentSkipRenderUnitTest extends \Tests\TestCase
{
    public function test_component_renders_like_normal()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $this->assertTrue(
            str($component->html())->contains([$component->id(), 'foo'])
        );
    }

    public function test_on_skip_render_render_is_not_called()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $component->assertSetStrict('skipped', false);
        $component->call('skip');
        $component->assertSetStrict('skipped', true);

        $this->assertNotNull($component->html());
    }

    public function test_with_skip_render_attribute_render_is_not_called()
    {
        $component = Livewire::test(ComponentSkipRenderAttributeStub::class);

        $component->assertSetStrict('skipped', false);
        $component->call('skip');
        $component->assertSetStrict('skipped', true);

        $this->assertNotNull($component->html());
    }

    public function test_on_redirect_in_mount_render_is_not_called()
    {
        Route::get('/403', ComponentSkipRenderOnRedirectHelperInMountStub::class);
        $this->get('/403')->assertRedirect('/bar');
    }
}

class ComponentSkipRenderStub extends Component
{
    public $skipped = false;

    public function skip()
    {
        $this->skipped = true;

        $this->skipRender();
    }

    public function render()
    {
        if ($this->skipped) {
            throw new \RuntimeException('Render should not be called after noop()');
        }

        return app('view')->make('null-view');
    }
}

class ComponentSkipRenderAttributeStub extends Component
{
    public $skipped = false;

    #[\Livewire\Attributes\Renderless]
    public function skip()
    {
        $this->skipped = true;
    }

    public function render()
    {
        if ($this->skipped) {
            throw new \RuntimeException('Render should not be called after noop()');
        }

        return app('view')->make('null-view');
    }
}

class ComponentSkipRenderOnRedirectInMountStub extends Component
{
    public function mount()
    {
        store($this)->set('redirect', '/yoyoyo');

        $this->skipRender();

        $this->redirect('/foo');
    }

    public function render()
    {
        throw new \RuntimeException('Render should not be called on redirect');
    }
}

class ComponentSkipRenderOnRedirectHelperInMountStub extends Component
{
    public function mount()
    {
        $this->skipRender();

        return redirect('/bar');
    }

    public function render()
    {
        throw new \RuntimeException('Render should not be called on redirect');
    }
}
