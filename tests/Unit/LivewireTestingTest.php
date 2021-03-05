<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;
use Illuminate\Support\Facades\Route;

class LivewireTestingTest extends TestCase
{
    /** @test */
    public function testing_livewire_route_works_with_user_route_with_the_same_signature()
    {
        Route::get('/{param1}/{param2}', function() {
            throw new \Exception('I shouldn\'t get executed!');
        });

        Livewire::test(HasMountArguments::class, ['name' => 'foo']);
    }

    /** @test */
    public function method_accepts_arguments_to_pass_to_mount()
    {
        $component = app(LivewireManager::class)
            ->test(HasMountArguments::class, ['name' => 'foo']);

        $this->assertStringContainsString('foo', $component->payload['effects']['html']);
    }

    /** @test */
    public function set_multiple_with_array()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, ['name' => 'foo'])
            ->set(['name' => 'bar'])
            ->assertSet('name', 'bar');
    }

    /** @test */
    public function assert_set()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, ['name' => 'foo'])
            ->assertSet('name', 'foo')
            ->set('name', 'info')
            ->assertSet('name', 'info')
            ->set('name', 'is_array')
            ->assertSet('name', 'is_array');
    }

    /** @test */
    public function assert_not_set()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, ['name' => 'bar'])
            ->assertNotSet('name', 'foo');
    }

    /** @test */
    public function assert_count()
    {
        app(LivewireManager::class)
            ->test(HasMountArgumentsButDoesntPassThemToBladeView::class, ['name' => ['foo']])
            ->assertCount('name', 1)
            ->set('name', ['foo', 'bar'])
            ->assertCount('name', 2)
            ->set('name', ['foo', 'bar', 'baz'])
            ->assertCount('name', 3)
            ->set('name', [])
            ->assertCount('name', 0);
    }

    /** @test */
    public function assert_see()
    {
        app(LivewireManager::class)
            ->test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertSee('should see me');
    }

    /** @test */
    public function assert_see_html()
    {
        app(LivewireManager::class)
            ->test(HasHtml::class)
            ->assertSeeHtml('<p style="display: none">Hello HTML</p>');
    }

    /** @test */
    public function assert_dont_see_html()
    {
        app(LivewireManager::class)
            ->test(HasHtml::class)
            ->assertDontSeeHtml('<span style="display: none">Hello HTML</span>');
    }

    /** @test */
    public function assert_see_doesnt_include_json_encoded_data_put_in_wire_data_attribute()
    {
        // See for more info: https://github.com/calebporzio/livewire/issues/62
        app(LivewireManager::class)
            ->test(HasMountArgumentsButDoesntPassThemToBladeView::class, ['name' => 'shouldnt see me'])
            ->assertDontSee('shouldnt see me');
    }

    /** @test */
    public function assert_emitted()
    {
        app(LivewireManager::class)
            ->test(EmitsEventsComponentStub::class)
            ->call('emitFoo')
            ->assertEmitted('foo')
            ->call('emitFooWithParam', 'bar')
            ->assertEmitted('foo', 'bar')
            ->call('emitFooWithParam', 'baz')
            ->assertEmitted('foo', function ($event, $params) {
                return $event === 'foo' && $params === ['baz'];
            });
    }

    /** @test */
    public function assert_not_emitted()
    {
        app(LivewireManager::class)
            ->test(EmitsEventsComponentStub::class)
            ->assertNotEmitted('foo')
            ->call('emitFoo')
            ->assertNotEmitted('bar')
            ->call('emitFooWithParam', 'not-bar')
            ->assertNotEmitted('foo', 'bar')
            ->call('emitFooWithParam', 'foo')
            ->assertNotEmitted('bar', 'foo')
            ->call('emitFooWithParam', 'baz')
            ->assertNotEmitted('bar', function ($event, $params) {
                return $event !== 'bar' && $params === ['baz'];
            })
            ->call('emitFooWithParam', 'baz')
            ->assertNotEmitted('foo', function ($event, $params) {
                return $event !== 'foo' && $params !== ['bar'];
            });
    }

    /** @test */
    public function assert_dispatched()
    {
        app(LivewireManager::class)
            ->test(DispatchesBrowserEventsComponentStub::class)
            ->call('dispatchFoo')
            ->assertDispatchedBrowserEvent('foo')
            ->call('dispatchFooWithData', ['bar' => 'baz'])
            ->assertDispatchedBrowserEvent('foo', ['bar' => 'baz'])
            ->call('dispatchFooWithData', ['bar' => 'baz'])
            ->assertDispatchedBrowserEvent('foo', function ($event, $data) {
                return $event === 'foo' && $data === ['bar' => 'baz'];
            });
    }

    /** @test */
    public function assert_has_error_with_manually_added_error()
    {
        app(LivewireManager::class)
            ->test(ValidatesDataWithSubmitStub::class)
            ->call('manuallyAddError')
            ->assertHasErrors('bob');
    }

    /** @test */
    public function assert_has_error_with_submit_validation()
    {
        app(LivewireManager::class)
            ->test(ValidatesDataWithSubmitStub::class)
            ->call('submit')
            ->assertHasErrors('foo')
            ->assertHasErrors(['foo', 'bar'])
            ->assertHasErrors([
                'foo' => ['required'],
                'bar' => ['required'],
            ]);
    }

    /** @test */
    public function assert_has_error_with_real_time_validation()
    {
        app(LivewireManager::class)
            ->test(ValidatesDataWithRealTimeStub::class)
            // ->set('foo', 'bar-baz')
            // ->assertHasNoErrors()
            ->set('foo', 'bar')
            ->assertHasErrors('foo')
            ->assertHasNoErrors('bar')
            ->assertHasErrors(['foo'])
            ->assertHasErrors([
                'foo' => ['min'],
            ])
            ->assertHasNoErrors([
                'foo' => ['required'],
            ])
            ->set('bar', '')
            ->assertHasErrors(['foo', 'bar']);
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
        return app('view')->make('show-name-with-this');
    }
}

class HasHtml extends Component
{
    public function render()
    {
        return app('view')->make('show-html');
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

class EmitsEventsComponentStub extends Component
{
    public function emitFoo()
    {
        $this->emit('foo');
    }

    public function emitFooWithParam($param)
    {
        $this->emit('foo', $param);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class DispatchesBrowserEventsComponentStub extends Component
{
    public function dispatchFoo()
    {
        $this->dispatchBrowserEvent('foo');
    }

    public function dispatchFooWithData($data)
    {
        $this->dispatchBrowserEvent('foo', $data);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ValidatesDataWithSubmitStub extends Component
{
    public $foo;
    public $bar;

    public function submit()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function manuallyAddError()
    {
        $this->addError('bob', 'lob');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ValidatesDataWithRealTimeStub extends Component
{
    public $foo;
    public $bar;

    public function updated($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required|min:6',
            'bar' => 'required',
        ]);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
