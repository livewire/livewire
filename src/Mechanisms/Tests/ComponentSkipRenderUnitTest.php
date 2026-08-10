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

        $component->update(calls: [
            ['method' => 'renderlessActionAttribute', 'params' => [], 'path' => ''],
            ['method' => 'updateName', 'params' => [], 'path' => ''],
        ]);

        $component->assertSetStrict('name', 'bar');
        $component->assertSee('bar');
    }

    public function test_render_not_skipped_when_a_renderless_action_call_is_batched_with_a_normal_call()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->assertSee('foo');

        $component->update(calls: [
            ['method' => 'renderlessActionCall', 'params' => [], 'path' => '', 'metadata' => ['renderless' => true]],
            ['method' => 'updateName', 'params' => [], 'path' => ''],
        ]);

        $component->assertSetStrict('name', 'bar');
        $component->assertSee('bar');
    }

    public function test_render_not_skipped_when_a_renderless_action_attribute_event_is_batched_with_a_normal_call()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->assertSee('foo');

        $component->update(calls: [
            ['method' => '__dispatch', 'params' => ['some-event', []], 'path' => ''],
            ['method' => 'updateName', 'params' => [], 'path' => ''],
        ]);

        $component->assertSetStrict('name', 'bar');
        $component->assertSee('bar');
    }

    public function test_renderless_batching_is_order_independent()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->update(calls: [
            ['method' => 'updateName', 'params' => [], 'path' => ''],
            ['method' => 'renderlessActionAttribute', 'params' => [], 'path' => ''],
        ]);

        $component->assertSee('bar');
    }

    public function test_render_is_skipped_when_all_batched_calls_are_renderless()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->update(calls: [
            ['method' => 'preventRender', 'params' => [], 'path' => ''],
            ['method' => 'renderlessActionCall', 'params' => [], 'path' => '', 'metadata' => ['renderless' => true]],
        ]);

        $component->assertSetStrict('renderShouldFail', true);
    }

    public function test_renderless_event_is_counted_as_a_renderless_call()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->update(calls: [
            ['method' => '__dispatch', 'params' => ['some-event', []], 'path' => ''],
            ['method' => 'preventRender', 'params' => [], 'path' => ''],
        ]);

        $component->assertSetStrict('renderShouldFail', true);
    }

    public function test_magic_action_makes_a_renderless_batch_render()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->update(calls: [
            ['method' => 'renderlessUpdateName', 'params' => [], 'path' => ''],
            ['method' => '$refresh', 'params' => [], 'path' => ''],
        ]);

        $component->assertSee('bar');
    }

    public function test_imperative_skip_render_still_skips_a_mixed_batch()
    {
        $component = Livewire::test(ComponentRenderlessBatchStub::class);

        $component->update(calls: [
            ['method' => 'updateName', 'params' => [], 'path' => ''],
            ['method' => 'skipRenderImperatively', 'params' => [], 'path' => ''],
        ]);

        $component->assertSetStrict('name', 'bar');
        $component->assertSetStrict('renderShouldFail', true);
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
    public $renderShouldFail = false;

    #[\Livewire\Attributes\Renderless]
    public function renderlessActionAttribute()
    {
        //
    }

    #[\Livewire\Attributes\Renderless]
    #[\Livewire\Attributes\On('some-event')]
    public function renderlessActionEventAttribute()
    {
        //
    }

    public function renderlessActionCall()
    {
        //
    }

    #[\Livewire\Attributes\Renderless]
    public function renderlessUpdateName()
    {
        $this->name = 'bar';
    }

    #[\Livewire\Attributes\Renderless]
    public function preventRender()
    {
        $this->renderShouldFail = true;
    }

    public function updateName()
    {
        $this->name = 'bar';
    }

    public function skipRenderImperatively()
    {
        $this->renderShouldFail = true;

        $this->skipRender();
    }

    public function render()
    {
        if ($this->renderShouldFail) {
            throw new \RuntimeException('Render should have been skipped');
        }

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
