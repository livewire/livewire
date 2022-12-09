<?php

namespace Livewire\Features\SupportUnitTesting;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\TestView;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

// TODO - Change this to \Tests\TestCase
class Test extends \LegacyTests\Unit\TestCase
{
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

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire('foo');
    }

    /** @test */
    public function can_assert_see_livewire_on_standard_blade_view_using_class_name()
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

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire(\App\Http\Livewire\Foo::class);
    }

    /** @test */
    public function assert_see_livewire_fails_when_the_component_is_not_present()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            public function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire('foo');
    }

    /** @test */
    public function assert_see_livewire_fails_when_the_component_is_not_present_using_class_name()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            public function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire(\App\Http\Livewire\Foo::class);
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

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire('foo');
    }

    /** @test */
    public function assert_dont_see_livewire_fails_when_the_component_is_present()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            public function getContent()
            {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire('foo');
    }

    /** @test */
    public function assert_dont_see_livewire_fails_when_the_component_is_present_using_class_name()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            public function getContent()
            {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire(\App\Http\Livewire\Foo::class);
    }

    /** @test */
    public function can_assert_dont_see_livewire_on_standard_blade_view_using_class_name()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            public function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire(\App\Http\Livewire\Foo::class);
    }

    /** @test */
    public function can_assert_see_livewire_on_test_view()
    {
        if(! class_exists(TestView::class)) {
            self::markTestSkipped('Need Laravel >= 8');
        }

        Artisan::call('make:livewire', ['name' => 'foo']);

        $testView = new TestView(view('render-component', [
            'component' => 'foo',
        ]));

        $testView->assertSeeLivewire('foo');
    }

    /** @test */
    public function can_assert_see_livewire_on_test_view_refering_by_subfolder_without_dot_index()
    {
        if(! class_exists(TestView::class)) {
            self::markTestSkipped('Need Laravel >= 8');
        }

        Artisan::call('make:livewire', ['name' => 'bar.index']);

        $testView = new TestView(view('render-component', [
            'component' => 'bar',
        ]));

        $testView->assertSeeLivewire('bar');
    }

    /** @test */
    public function can_assert_dont_see_livewire_on_test_view()
    {
        if(! class_exists(TestView::class)) {
            self::markTestSkipped('Need Laravel >= 8');
        }

        Artisan::call('make:livewire', ['name' => 'foo']);

        $testView = new TestView(view('null-view'));

        $testView->assertDontSeeLivewire('foo');
    }
    /** @test */
    public function cant_test_non_livewire_components()
    {
        $this->expectException(\Exception::class);

        Livewire::test(\StdClass::class);
    }

    /** @test */
    public function livewire_route_works_with_user_route_with_the_same_signature()
    {
        Route::get('/{param1}/{param2}', function() {
            throw new \Exception('I shouldn\'t get executed!');
        });

        Livewire::test(HasMountArguments::class, ['name' => 'foo']);

        $this->assertTrue(true);
    }

    /** @test */
    public function method_accepts_arguments_to_pass_to_mount()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'foo']);

        $this->assertStringContainsString('foo', $component->html());
    }

    /** @test */
    public function set_multiple_with_array()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'foo'])
            ->set(['name' => 'bar'])
            ->assertSet('name', 'bar');
    }

    /** @test */
    public function assert_set()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'foo'])
            ->assertSet('name', 'foo')
            ->set('name', 'info')
            ->assertSet('name', 'info')
            ->set('name', 'is_array')
            ->assertSet('name', 'is_array')
            ->set('name', 0)
            ->assertSet('name', null)
            ->assertSet('name', 0, true)
            ->assertSet(
                'name',
                function ($propertyValue) {
                    return $propertyValue === 0;
                }
            );

        $this->expectException(\PHPUnit\Framework\ExpectationFailedException::class);

        $component->assertSet('name', null, true);
    }

    /** @test */
    public function assert_not_set()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'bar'])
            ->assertNotSet('name', 'foo')
            ->set('name', 100)
            ->assertNotSet('name', "1e2", true)
            ->set('name', 0)
            ->assertNotSet('name', false, true)
            ->assertNotSet('name', null, true);

        $this->expectException(\PHPUnit\Framework\ExpectationFailedException::class);

        $component->assertNotSet('name', null);
    }

    /** @test */
    public function assert_count()
    {
        Livewire::test(HasMountArgumentsButDoesntPassThemToBladeView::class, ['name' => ['foo']])
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
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertSee('should see me');
    }

    /** @test */
    public function assert_see_unescaped()
    {
        Livewire::test(HasHtml::class)
                ->assertSee('<p style', false);
    }

    /** @test */
    public function assert_see_multiple()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertSee(['should', 'see', 'me']);
    }

    /** @test */
    public function assert_see_html()
    {
        Livewire::test(HasHtml::class)
            ->assertSeeHtml('<p style="display: none">Hello HTML</p>');
    }

    /** @test */
    public function assert_dont_see_html()
    {
        Livewire::test(HasHtml::class)
            ->assertDontSeeHtml('<span style="display: none">Hello HTML</span>');
    }

    /** @test */
    public function assert_dont_see()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertDontSee('no one should see this');
    }

    /** @test */
    public function assert_dont_see_unescaped()
    {
        Livewire::test(HasHtml::class)
                ->assertDontSee('<span>', false);
    }

    /** @test */
    public function assert_dont_see_multiple()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertDontSee(['no', 'one', 'really']);
    }

    /** @test */
    public function assert_see_doesnt_include_wire_id_and_wire_data_attribute()
    {
        /*
        * See for more info: https://github.com/calebporzio/livewire/issues/62
        * Regex test: https://regex101.com/r/UhjREC/2/
        */
        Livewire::test(HasMountArgumentsButDoesntPassThemToBladeView::class, ['name' => 'shouldnt see me'])
            ->assertDontSee('shouldnt see me');
    }

    /** @test */
    public function assert_emitted()
    {
        Livewire::test(EmitsEventsComponentStub::class)
            ->call('emitFoo')
            ->assertEmitted('foo')
            ->call('emitFooWithParam', 'bar')
            ->assertEmitted('foo', 'bar')
            ->call('emitFooWithParam', 'info')
            ->assertEmitted('foo', 'info')
            ->call('emitFooWithParam', 'last')
            ->assertEmitted('foo', 'last')
            ->call('emitFooWithParam', 'retry')
            ->assertEmitted('foo', 'retry')
            ->call('emitFooWithParam', 'baz')
            ->assertEmitted('foo', function ($event, $params) {
                return $event === 'foo' && $params === ['baz'];
            });
    }

    /** @test */
    public function assert_emitted_to()
    {
        Livewire::component('some-component', SomeComponentStub::class);

        Livewire::test(EmitsEventsComponentStub::class)
            ->call('emitFooToSomeComponent')
            ->assertEmittedTo('some-component', 'foo')
            ->call('emitFooToAComponentAsAModel')
            ->assertEmittedTo(ComponentWhichReceivesEvent::class, 'foo')
            ->call('emitFooToSomeComponentWithParam', 'bar')
            ->assertEmittedTo('some-component', 'foo', 'bar')
            ->call('emitFooToSomeComponentWithParam', 'bar')
            ->assertEmittedTo('some-component','foo', function ($event, $params) {
                return $event === 'foo' && $params === ['bar'];
            })
        ;
    }

    /** @test */
    public function assert_emitted_up()
    {
        Livewire::test(EmitsEventsComponentStub::class)
            ->call('emitFooUp')
            ->assertEmittedUp('foo')
            ->call('emitFooUpWithParam', 'bar')
            ->assertEmittedUp('foo', 'bar')
            ->call('emitFooUpWithParam', 'bar')
            ->assertEmittedUp('foo', function ($event, $params) {
                return $event === 'foo' && $params === ['bar'];
            })
        ;
    }

    /** @test */
    public function assert_not_emitted()
    {
        Livewire::test(EmitsEventsComponentStub::class)
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
    public function assert_dispatched_browser_event()
    {
        Livewire::test(DispatchesBrowserEventsComponentStub::class)
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
    public function assert_dispatched_browser_event_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::test(DispatchesBrowserEventsComponentStub::class)
            ->assertDispatchedBrowserEvent('foo');
    }

    /** @test */
    public function assert_has_error_with_manually_added_error()
    {
        Livewire::test(ValidatesDataWithSubmitStub::class)
            ->call('manuallyAddError')
            ->assertHasErrors('bob');
    }

    /** @test */
    public function assert_has_error_with_submit_validation()
    {
        Livewire::test(ValidatesDataWithSubmitStub::class)
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
        Livewire::test(ValidatesDataWithRealTimeStub::class)
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

    /** @test */
    public function it_ignores_rules_with_params(){
        Livewire::test(ValidatesDataWithRulesHasParams::class)
            ->call('submit')
            ->assertHasErrors(['foo' => 'min'])
            ->assertHasErrors(['foo' => 'min:2'])
            ->set('foo', 'FOO')
            ->assertHasNoErrors(['foo' => 'min'])
            ->assertHasNoErrors(['foo' => 'min:2']);
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
        return '<div><p style="display: none">Hello HTML</p></div>';
    }
}

class SomeComponentStub extends Component
{
    public function render()
    {
        return app('view')->make('null-view');
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

    public function emitFooToSomeComponent()
    {
        $this->emitTo('some-component','foo');
    }

    public function emitFooToSomeComponentWithParam($param)
    {
        $this->emitTo('some-component','foo', $param);
    }

    public function emitFooToAComponentAsAModel()
    {
        $this->emitTo(ComponentWhichReceivesEvent::class,'foo');
    }

    public function emitFooUp()
    {
        $this->emitUp('foo');
    }

    public function emitFooUpWithParam($param)
    {
        $this->emitUp('foo', $param);
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

class ValidatesDataWithRulesHasParams extends Component{
    public $foo, $bar;

    public function submit()
    {
        $this->validate([
            'foo' => 'string|min:2',
        ]);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWhichReceivesEvent extends Component
{

}