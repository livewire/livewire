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

    public function test_render_not_skipped_when_a_renderless_action_attribute_is_batched_with_a_normal_call()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->assertSee('foo');

        // Simulate the browser batching two $wire calls into a single request,
        // e.g. `$wire.renderlessAction(); $wire.updateName()`.
        $component->update(calls: [
            ['method' => 'renderlessActionAttribute', 'params' => [], 'path' => ''],
            ['method' => 'updateName', 'params' => [], 'path' => ''],
        ]);

        // The non-renderless updateName() mutated state the view depends on...
        $component->assertSetStrict('name', 'bar');

        // ...so the component should re-render, despite the batched renderless call.
        $component->assertSee('bar');
    }

    public function test_render_not_skipped_when_a_renderless_action_call_is_batched_with_a_normal_call()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->assertSee('foo');

        // Simulate the browser batching two $wire calls into a single request,
        // e.g. `$wire.renderlessAction(); $wire.updateName()`.
        $component->update(calls: [
            ['method' => 'renderlessActionCall', 'params' => [], 'path' => '', 'metadata' => ['renderless' => true]],
            ['method' => 'updateName', 'params' => [], 'path' => ''],
        ]);

        // The non-renderless updateName() mutated state the view depends on...
        $component->assertSetStrict('name', 'bar');

        // ...so the component should re-render, despite the batched renderless call.
        $component->assertSee('bar');
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

class ComponentRenderlessBatchStub extends Component
{
    public $name = 'foo';

    #[\Livewire\Attributes\Renderless]
    public function renderlessActionAttribute()
    {
        //
    }

    public function renderlessActionCall()
    {
        //
    }

    public function updateName()
    {
        $this->name = 'bar';
    }

    public function render()
    {
        return <<<'HTML'
            <div>{{ $name }}</div>
        HTML;
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
