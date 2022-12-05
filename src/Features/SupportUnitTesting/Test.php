<?php

namespace Livewire\Features\SupportUnitTesting;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\TestView;
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
}
