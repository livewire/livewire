<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Contracts\Validation\ValidationRule;
use PHPUnit\Framework\ExpectationFailedException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\TestView;
use Livewire\Component;
use Livewire\Livewire;
use Closure;

// TODO - Change this to \Tests\TestCase
class UnitTest extends \LegacyTests\Unit\TestCase
{
    /** @test */
    function can_assert_see_livewire_on_standard_blade_view()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
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
    function can_test_component_using_magic_render()
    {
        mkdir($this->livewireViewsPath());
        file_put_contents($this->livewireViewsPath().'/foo.blade.php', <<<'PHP'
        <div>
            Im foo
        </div>
        PHP);

        mkdir($this->livewireClassesPath());
        file_put_contents($this->livewireClassesPath().'/Foo.php', <<<'PHP'
        <?php

        namespace App\Livewire;

        use Livewire\Component;

        class Foo extends Component
        {
            //
        }
        PHP);

        Livewire::test('foo')->assertSee('Im foo');
    }

    /** @test */
    function can_assert_see_livewire_on_standard_blade_view_using_class_name()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
            {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire(\App\Livewire\Foo::class);
    }

    /** @test */
    function assert_see_livewire_fails_when_the_component_is_not_present()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire('foo');
    }

    /** @test */
    function assert_see_livewire_fails_when_the_component_is_not_present_using_class_name()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertSeeLivewire(\App\Livewire\Foo::class);
    }

    /** @test */
    function can_assert_dont_see_livewire_on_standard_blade_view()
    {
        $fakeClass = new class {
            function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire('foo');
    }

    /** @test */
    function assert_dont_see_livewire_fails_when_the_component_is_present()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
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
    function assert_dont_see_livewire_fails_when_the_component_is_present_using_class_name()
    {
        $this->expectException(ExpectationFailedException::class);

        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
            {
                return view('render-component', [
                    'component' => 'foo',
                ])->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire(\App\Livewire\Foo::class);
    }

    /** @test */
    function can_assert_dont_see_livewire_on_standard_blade_view_using_class_name()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $fakeClass = new class {
            function getContent()
            {
                return view('null-view')->render();
            }
        };

        $testResponse = new TestResponse($fakeClass);

        $testResponse->assertDontSeeLivewire(\App\Livewire\Foo::class);
    }

    /** @test */
    function can_assert_see_livewire_on_test_view()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $testView = new TestView(view('render-component', [
            'component' => 'foo',
        ]));

        $testView->assertSeeLivewire('foo');
    }

    /** @test */
    function can_assert_see_livewire_on_test_view_refering_by_subfolder_without_dot_index()
    {
        Artisan::call('make:livewire', ['name' => 'bar.index']);

        $testView = new TestView(view('render-component', [
            'component' => 'bar',
        ]));

        $testView->assertSeeLivewire('bar');
    }

    /** @test */
    function can_assert_dont_see_livewire_on_test_view()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $testView = new TestView(view('null-view'));

        $testView->assertDontSeeLivewire('foo');
    }
    /** @test */
    function cant_test_non_livewire_components()
    {
        $this->expectException(\Exception::class);

        Livewire::test(\StdClass::class);
    }

    /** @test */
    function livewire_route_works_with_user_route_with_the_same_signature()
    {
        Route::get('/{param1}/{param2}', function() {
            throw new \Exception('I shouldn\'t get executed!');
        });

        Livewire::test(HasMountArguments::class, ['name' => 'foo']);

        $this->assertTrue(true);
    }

    /** @test */
    function method_accepts_arguments_to_pass_to_mount()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'foo']);

        $this->assertStringContainsString('foo', $component->html());
    }

    /** @test */
    function set_multiple_with_array()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'foo'])
            ->set(['name' => 'bar'])
            ->assertSet('name', 'bar');
    }

    /** @test */
    function set_for_backed_enums()
    {
        Livewire::test(ComponentWithEnums::class)
            ->set('backedFooBarEnum', BackedFooBarEnum::FOO->value)
            ->assertSetStrict('backedFooBarEnum', BackedFooBarEnum::FOO)
            ->set('backedFooBarEnum', BackedFooBarEnum::FOO)
            ->assertSetStrict('backedFooBarEnum', BackedFooBarEnum::FOO);
    }

    /** @test */
    function assert_set()
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
    function assert_not_set()
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
    function assert_set_strict()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'foo'])
            ->set('name', '')
            ->assertSetStrict('name', '');

        $this->expectException(\PHPUnit\Framework\ExpectationFailedException::class);

        $component->assertSetStrict('name', null);
    }

    /** @test */
    function assert_not_set_strict()
    {
        $component = Livewire::test(HasMountArguments::class, ['name' => 'bar'])
            ->set('name', '')
            ->assertNotSetStrict('name', null);

        $this->expectException(\PHPUnit\Framework\ExpectationFailedException::class);

        $component->assertNotSetStrict('name', '');
    }

    /** @test */
    function assert_count()
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
    function assert_see()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertSee('should see me');
    }

    /** @test */
    function assert_see_unescaped()
    {
        Livewire::test(HasHtml::class)
                ->assertSee('<p style', false);
    }

    /** @test */
    function assert_see_multiple()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertSee(['should', 'see', 'me']);
    }

    /** @test */
    function assert_see_html()
    {
        Livewire::test(HasHtml::class)
            ->assertSeeHtml('<p style="display: none">Hello HTML</p>');
    }

    /** @test */
    function assert_dont_see_html()
    {
        Livewire::test(HasHtml::class)
            ->assertDontSeeHtml('<span style="display: none">Hello HTML</span>');
    }

    /** @test */
    function assert_dont_see()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertDontSee('no one should see this');
    }

    /** @test */
    function assert_dont_see_unescaped()
    {
        Livewire::test(HasHtml::class)
                ->assertDontSee('<span>', false);
    }

    /** @test */
    function assert_dont_see_multiple()
    {
        Livewire::test(HasMountArguments::class, ['name' => 'should see me'])
            ->assertDontSee(['nobody', 'really', 'knows']);
    }

    /** @test */
    function assert_see_doesnt_include_wire_id_and_wire_data_attribute()
    {
        /*
        * See for more info: https://github.com/calebporzio/livewire/issues/62
        * Regex test: https://regex101.com/r/UhjREC/2/
        */
        Livewire::test(HasMountArgumentsButDoesntPassThemToBladeView::class, ['name' => 'shouldnt see me'])
            ->assertDontSee('shouldnt see me');
    }

    /** @test */
    function assert_dispatched()
    {
        Livewire::test(DispatchesEventsComponentStub::class)
            ->call('dispatchFoo')
            ->assertDispatched('foo')
            ->call('dispatchFooWithParam', 'bar')
            ->assertDispatched('foo', 'bar')
            ->call('dispatchFooWithParam', 'info')
            ->assertDispatched('foo', 'info')
            ->call('dispatchFooWithParam', 'last')
            ->assertDispatched('foo', 'last')
            ->call('dispatchFooWithParam', 'retry')
            ->assertDispatched('foo', 'retry')
            ->call('dispatchFooWithParam', 'baz')
            ->assertDispatched('foo', function ($event, $params) {
                return $event === 'foo' && $params === ['baz'];
            });
    }

    /** @test */
    function assert_dispatched_to()
    {
        Livewire::component('some-component', SomeComponentStub::class);

        Livewire::test(DispatchesEventsComponentStub::class)
            ->call('dispatchFooToSomeComponent')
            ->assertDispatchedTo('some-component', 'foo')
            ->call('dispatchFooToAComponentAsAModel')
            ->assertDispatchedTo(ComponentWhichReceivesEvent::class, 'foo')
            ->call('dispatchFooToSomeComponentWithParam', 'bar')
            ->assertDispatchedTo('some-component', 'foo', 'bar')
            ->call('dispatchFooToSomeComponentWithParam', 'bar')
            ->assertDispatchedTo('some-component','foo', function ($event, $params) {
                return $event === 'foo' && $params === ['bar'];
            })
        ;
    }

    /** @test */
    function assert_not_dispatched()
    {
        Livewire::test(DispatchesEventsComponentStub::class)
            ->assertNotDispatched('foo')
            ->call('dispatchFoo')
            ->assertNotDispatched('bar')
            ->call('dispatchFooWithParam', 'not-bar')
            ->assertNotDispatched('foo', 'bar')
            ->call('dispatchFooWithParam', 'foo')
            ->assertNotDispatched('bar', 'foo')
            ->call('dispatchFooWithParam', 'baz')
            ->assertNotDispatched('bar', function ($event, $params) {
                return $event !== 'bar' && $params === ['baz'];
            })
            ->call('dispatchFooWithParam', 'baz')
            ->assertNotDispatched('foo', function ($event, $params) {
                return $event !== 'foo' && $params !== ['bar'];
            });
    }

    /** @test */
    function assert_has_errors()
    {
        Livewire::test(ValidatesDataWithSubmitStub::class)
            ->call('submit')
            ->assertHasErrors()
            ->assertHasErrors('foo')
            ->assertHasErrors(['foo'])
            ->assertHasErrors(['foo' => 'required'])
            ->assertHasErrors(['foo' => 'The foo field is required.'])
            ->assertHasErrors(['foo' => 'required', 'bar' => 'required'])
            ->assertHasErrors(['foo' => 'The foo field is required.', 'bar' => 'The bar field is required.'])
            ->assertHasErrors(['foo' => ['The foo field is required.'], 'bar' => ['The bar field is required.']])
            ->assertHasErrors(['foo' => function ($rules, $messages) {
                return in_array('required', $rules) && in_array('The foo field is required.', $messages);
            }])
        ;
    }

    /** @test */
    function assert_has_errors_with_validation_class()
    {
        Livewire::test(ValidatesDataWithCustomRuleStub::class)
            ->call('submit')
            ->assertHasErrors()
            ->assertHasErrors('foo')
            ->assertHasErrors(['foo'])
            ->assertHasErrors(['foo' => CustomValidationRule::class])
            ->assertHasErrors(['foo' => 'My custom message'])
            ->assertHasErrors(['foo' => function ($rules, $messages) {
                return in_array(CustomValidationRule::class, $rules) && in_array('My custom message', $messages);
            }])
            ->set('foo', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertHasNoErrors('foo')
            ->assertHasNoErrors(['foo'])
            ->assertHasNoErrors(['foo' => CustomValidationRule::class])
            ->assertHasNoErrors(['foo' => 'My custom message'])
        ;
    }

    /** @test */
    function assert_has_error_with_manually_added_error()
    {
        Livewire::test(ValidatesDataWithSubmitStub::class)
            ->call('manuallyAddError')
            ->assertHasErrors('bob');
    }

    /** @test */
    function assert_has_error_with_submit_validation()
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
    function assert_has_error_with_real_time_validation()
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
    function it_ignores_rules_with_params()
    {
        Livewire::test(ValidatesDataWithRulesHasParams::class)
            ->call('submit')
            ->assertHasErrors(['foo' => 'min'])
            ->assertHasErrors(['foo' => 'min:2'])
            ->set('foo', 'FOO')
            ->assertHasNoErrors(['foo' => 'min'])
            ->assertHasNoErrors(['foo' => 'min:2']);
    }

    /** @test */
    function assert_response_of_calling_method()
    {
        Livewire::test(ComponentWithMethodThatReturnsData::class)
            ->call('foo')
            ->assertReturned('bar')
            ->assertReturned(fn ($data) => $data === 'bar');
    }

    /** @test */
    public function can_set_cookies_for_use_with_testing()
    {
        // Test both the `withCookies` and `withCookie` methods that Laravel normally provides
        Livewire::withCookies(['colour' => 'blue'])
            ->withCookie('name', 'Taylor')
            ->test(new class extends Component {
                public $colourCookie = '';
                public $nameCookie = '';
                public function mount()
                {
                    $this->colourCookie = request()->cookie('colour');
                    $this->nameCookie = request()->cookie('name');
                }

                public function render()
                {
                    return '<div></div>';
                }
            })
            ->assertSet('colourCookie', 'blue')
            ->assertSet('nameCookie', 'Taylor')
            ;
    }

    /** @test */
    public function can_set_headers_for_use_with_testing()
    {
        Livewire::withHeaders(['colour' => 'blue', 'name' => 'Taylor'])
            ->test(new class extends Component {
                public $colourHeader = '';
                public $nameHeader = '';
                public function mount()
                {
                    $this->colourHeader = request()->header('colour');
                    $this->nameHeader = request()->header('name');
                }

                public function render()
                {
                    return '<div></div>';
                }
            })
            ->assertSet('colourHeader', 'blue')
            ->assertSet('nameHeader', 'Taylor')
            ;
    }

    /** @test */
    public function can_set_cookies_and_use_it_for_testing_subsequent_request()
    {
        // Test both the `withCookies` and `withCookie` methods that Laravel normally provides
        Livewire::withCookies(['colour' => 'blue'])->withCookie('name', 'Taylor')
            ->test(new class extends Component {
                public $colourCookie = '';
                public $nameCookie = '';

                public function setTheCookies()
                {
                    $this->colourCookie = request()->cookie('colour');
                    $this->nameCookie = request()->cookie('name');
                }

                public function render()
                {
                    return '<div></div>';
                }
            })
            ->call('setTheCookies')
            ->assertSet('colourCookie', 'blue')
            ->assertSet('nameCookie', 'Taylor');
    }
}

class HasMountArguments extends Component
{
    public $name;

    function mount($name)
    {
        $this->name = $name;
    }

    function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class HasHtml extends Component
{
    function render()
    {
        return '<div><p style="display: none">Hello HTML</p></div>';
    }
}

class SomeComponentStub extends Component
{
    function render()
    {
        return app('view')->make('null-view');
    }
}

class HasMountArgumentsButDoesntPassThemToBladeView extends Component
{
    public $name;

    function mount($name)
    {
        $this->name = $name;
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class DispatchesEventsComponentStub extends Component
{
    function dispatchFoo()
    {
        $this->dispatch('foo');
    }

    function dispatchFooWithParam($param)
    {
        $this->dispatch('foo', $param);
    }

    function dispatchFooToSomeComponent()
    {
        $this->dispatch('foo')->to('some-component');
    }

    function dispatchFooToSomeComponentWithParam($param)
    {
        $this->dispatch('foo', $param)->to('some-component');
    }

    function dispatchFooToAComponentAsAModel()
    {
        $this->dispatch('foo')->to(ComponentWhichReceivesEvent::class);
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class CustomValidationRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === false) {
            $fail('My custom message');
        }
    }
}

class ValidatesDataWithCustomRuleStub extends Component
{
    public bool $foo = false;

    function submit()
    {
        $this->validate([
            'foo' => new CustomValidationRule,
        ]);
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class ValidatesDataWithSubmitStub extends Component
{
    public $foo;
    public $bar;

    function submit()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    function manuallyAddError()
    {
        $this->addError('bob', 'lob');
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class ValidatesDataWithRealTimeStub extends Component
{
    public $foo;
    public $bar;

    function updated($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required|min:6',
            'bar' => 'required',
        ]);
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class ValidatesDataWithRulesHasParams extends Component{
    public $foo, $bar;

    function submit()
    {
        $this->validate([
            'foo' => 'string|min:2',
        ]);
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWhichReceivesEvent extends Component
{

}

class ComponentWithMethodThatReturnsData extends Component
{
    function foo()
    {
        return 'bar';
    }

    function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWithEnums extends Component
{
    public BackedFooBarEnum $backedFooBarEnum;

    function render()
    {
        return app('view')->make('null-view');
    }
}

enum BackedFooBarEnum : string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
