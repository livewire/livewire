<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Livewire\Component;
use Livewire\Livewire;

class TraitsUnitTest extends \Tests\TestCase
{
    public function test_traits_can_intercept_lifecycle_hooks()
    {
        Livewire::test(ComponentWithTraitStub::class)
            ->assertSetStrict(
                'hooksFromTrait',
                ['initialized', 'mount', 'rendering', 'rendered:show-name', 'dehydrate']
            )
            ->set('foo', 'bar')
            ->assertSetStrict(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'updating:foobar', 'updated:foobar', 'rendering', 'rendered:show-name', 'dehydrate']
            )
            ->call(
                'testExceptionInterceptor',
            )
            ->assertSetStrict(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'exception', 'rendering', 'rendered:show-name', 'dehydrate']
            );
    }

    public function test_multiple_traits_can_intercept_lifecycle_hooks()
    {
        Livewire::test(ComponentWithTwoTraitsStub::class)
            ->assertSetStrict('hooksFromTrait', [
                'initialized', 'secondInitialized',
                'mount', 'secondMount',
                'rendering', 'secondRendering',
                'rendered:show-name', 'secondRendered:show-name',
                'dehydrate', 'secondDehydrate'
            ])
            ->set('foo', 'bar')
            ->assertSetStrict('hooksFromTrait', [
                'initialized', 'secondInitialized',
                'hydrate', 'secondHydrate',
                'updating:foobar', 'secondUpdating:foobar',
                'updated:foobar', 'secondUpdated:foobar',
                'rendering', 'secondRendering',
                'rendered:show-name', 'secondRendered:show-name',
                'dehydrate', 'secondDehydrate'
            ]);
    }

    public function test_calling_test_methods_will_not_run_hooks_from_previous_methods()
    {
        ComponentForTestMethodsStub::$hooksFromTrait = [];
        $test = Livewire::test(ComponentForTestMethodsStub::class);
        $this->assertEquals(
            ['initialized', 'mount', 'rendering', 'rendered:show-name', 'dehydrate',],
            ComponentForTestMethodsStub::$hooksFromTrait
        );

        ComponentForTestMethodsStub::$hooksFromTrait = [];
        $test->set('foo', 'bar');
        $this->assertEquals(
            ['initialized', 'hydrate', 'updating:foobar', 'updated:foobar', 'rendering', 'rendered:show-name', 'dehydrate'],
            ComponentForTestMethodsStub::$hooksFromTrait
        );

        ComponentForTestMethodsStub::$hooksFromTrait = [];
        $test->call('save');
        $this->assertEquals(
            ['initialized', 'hydrate', 'rendering', 'rendered:show-name', 'dehydrate'],
            ComponentForTestMethodsStub::$hooksFromTrait
        );
    }

    public function test_trait_hooks_are_run_at_the_same_time_as_component_hooks()
    {
        Livewire::test(ComponentWithTraitStubAndComponentLifecycleHooks::class)
            ->assertSetStrict(
                'hooks',
                [
                    'bootcomponent',
                    'boottrait',
                    'initializedtrait',
                    'mountcomponent',
                    'mounttrait',
                    'bootedcomponent',
                    'bootedtrait',
                    'rendercomponent',
                    'renderingcomponent',
                    'renderingtrait',
                    'renderedcomponent:show-name',
                    'renderedtrait:show-name',
                    'dehydratecomponent',
                    'dehydratetrait',
                ]
            )
            ->set('foo', 'bar')
            ->assertSetStrict(
                'hooks',
                [
                    'bootcomponent',
                    'boottrait',
                    'initializedtrait',
                    'hydratecomponent',
                    'hydratetrait',
                    'bootedcomponent',
                    'bootedtrait',
                    'updatingcomponent:foobar',
                    'updatingtrait:foobar',
                    'updatedcomponent:foobar',
                    'updatedtrait:foobar',
                    'rendercomponent',
                    'renderingcomponent',
                    'renderingtrait',
                    'renderedcomponent:show-name',
                    'renderedtrait:show-name',
                    'dehydratecomponent',
                    'dehydratetrait',
                ]
            )
            ->call('testExceptionInterceptor')
            ->assertSetStrict(
                'hooks',
                [
                    'bootcomponent',
                    'boottrait',
                    'initializedtrait',
                    'hydratecomponent',
                    'hydratetrait',
                    'bootedcomponent',
                    'bootedtrait',
                    'exceptioncomponent',
                    'exceptiontrait',
                    'rendercomponent',
                    'renderingcomponent',
                    'renderingtrait',
                    'renderedcomponent:show-name',
                    'renderedtrait:show-name',
                    'dehydratecomponent',
                    'dehydratetrait',
                ]
                );
    }
}
class CustomException extends \Exception {};


trait TraitForComponent
{
    public function mountTraitForComponent()
    {
        $this->hooksFromTrait[] = 'mount';
    }

    public function hydrateTraitForComponent()
    {
        $this->hooksFromTrait[] = 'hydrate';
    }

    public function dehydrateTraitForComponent()
    {
        $this->hooksFromTrait[] = 'dehydrate';
    }

    public function updatingTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'updating:'.$name.$value;
    }

    public function updatedTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'updated:'.$name.$value;
    }

    public function renderingTraitForComponent()
    {
        $this->hooksFromTrait[] = 'rendering';
    }

    public function renderedTraitForComponent($view)
    {
        $this->hooksFromTrait[] = 'rendered:'.$view->getName();
    }

    public function initializeTraitForComponent()
    {
        // Reset from previous requests.
        $this->hooksFromTrait = [];

        $this->hooksFromTrait[] = 'initialized';
    }
    public function exceptionTraitForComponent($e, $stopPropagation)
    {
        if($e instanceof CustomException) {
            $this->hooksFromTrait[] = 'exception';
            $stopPropagation();
        }
    }
}

class ComponentWithTraitStub extends Component
{
    use TraitForComponent;

    public $hooksFromTrait = [];

    public $foo = 'bar';

    public function testExceptionInterceptor()
    {
        throw new CustomException;
    }

    public function render()
    {
        return view('show-name', ['name' => $this->foo]);
    }
}

trait SecondTraitForComponent
{
    public function mountSecondTraitForComponent()
    {
        $this->hooksFromTrait[] = 'secondMount';
    }

    public function hydrateSecondTraitForComponent()
    {
        $this->hooksFromTrait[] = 'secondHydrate';
    }

    public function dehydrateSecondTraitForComponent()
    {
        $this->hooksFromTrait[] = 'secondDehydrate';
    }

    public function updatingSecondTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'secondUpdating:'.$name.$value;
    }

    public function updatedSecondTraitForComponent($name, $value)
    {
        $this->hooksFromTrait[] = 'secondUpdated:'.$name.$value;
    }

    public function renderingSecondTraitForComponent()
    {
        $this->hooksFromTrait[] = 'secondRendering';
    }

    public function renderedSecondTraitForComponent($view)
    {
        $this->hooksFromTrait[] = 'secondRendered:'.$view->getName();
    }

    public function initializeSecondTraitForComponent()
    {
        $this->hooksFromTrait[] = 'secondInitialized';
    }

}

class ComponentWithTwoTraitsStub extends ComponentWithTraitStub
{
    use TraitForComponent, SecondTraitForComponent;
}

trait TraitForComponentForTestMethods
{
    public function mountTraitForComponentForTestMethods()
    {
        static::$hooksFromTrait[] = 'mount';
    }

    public function hydrateTraitForComponentForTestMethods()
    {
        static::$hooksFromTrait[] = 'hydrate';
    }

    public function dehydrateTraitForComponentForTestMethods()
    {
        static::$hooksFromTrait[] = 'dehydrate';
    }

    public function updatingTraitForComponentForTestMethods($name, $value)
    {
        static::$hooksFromTrait[] = 'updating:'.$name.$value;
    }

    public function updatedTraitForComponentForTestMethods($name, $value)
    {
        static::$hooksFromTrait[] = 'updated:'.$name.$value;
    }

    public function renderingTraitForComponentForTestMethods()
    {
        static::$hooksFromTrait[] = 'rendering';
    }

    public function renderedTraitForComponentForTestMethods($view)
    {
        static::$hooksFromTrait[] = 'rendered:'.$view->getName();
    }

    public function initializeTraitForComponentForTestMethods()
    {
        static::$hooksFromTrait[] = 'initialized';
    }
}

class ComponentForTestMethodsStub extends Component
{
    use TraitForComponentForTestMethods;

    /*
     * Livewire tests will boot a new instance with each test method (ie. set(), call())
     * Hence using static variable to track hooks across all instances
     */
    public static $hooksFromTrait = [];

    public $foo = 'bar';

    public function save()
    {
        //
    }

    public function render()
    {
        return view('show-name', ['name' => $this->foo]);
    }
}

trait TraitForComponentWithComponentHooks
{
    public function bootTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'boottrait';
    }

    public function mountTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'mounttrait';
    }

    public function bootedTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'bootedtrait';
    }

    public function hydrateTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'hydratetrait';
    }

    public function dehydrateTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'dehydratetrait';
    }

    public function updatingTraitForComponentWithComponentHooks($name, $value)
    {
        $this->hooks[] = 'updatingtrait:'.$name.$value;
    }

    public function updatedTraitForComponentWithComponentHooks($name, $value)
    {
        $this->hooks[] = 'updatedtrait:'.$name.$value;
    }

    public function renderingTraitForComponentWithComponentHooks()
    {
        $this->hooks[] = 'renderingtrait';
    }

    public function renderedTraitForComponentWithComponentHooks($view)
    {
        $this->hooks[] = 'renderedtrait:'.$view->getName();
    }

    public function initializeTraitForComponentWithComponentHooks()
    {$this->hooks[] = 'initializedtrait';
    }
    public function exceptionTraitForComponentWithComponentHooks($e, $stopPropagation) {
        if($e instanceof CustomException) {
            $this->hooks[] = 'exceptiontrait';
            $stopPropagation();
        }
    }
}

class ComponentWithTraitStubAndComponentLifecycleHooks extends Component
{
    use TraitForComponentWithComponentHooks;

    public $hooks = [];

    public $foo = 'bar';

    public function boot()
    {
        // Reset from previous requests.
        $this->hooks = [];

        $this->hooks[] = 'bootcomponent';
    }

    public function mount()
    {
        $this->hooks[] = 'mountcomponent';
    }

    public function booted()
    {
        $this->hooks[] = 'bootedcomponent';
    }

    public function hydrate()
    {
        $this->hooks[] = 'hydratecomponent';
    }

    public function dehydrate()
    {
        $this->hooks[] = 'dehydratecomponent';
    }

    public function updating($name, $value)
    {
        $this->hooks[] = 'updatingcomponent:'.$name.$value;
    }

    public function updated($name, $value)
    {
        $this->hooks[] = 'updatedcomponent:'.$name.$value;
    }

    public function rendering()
    {
        $this->hooks[] = 'renderingcomponent';
    }

    public function rendered($view)
    {
        $this->hooks[] = 'renderedcomponent:'.$view->getName();
    }

    public function initialize()
    {
        // Reset from previous requests.
        $this->hooks = [];

        $this->hooks[] = 'initializedcomponent';
    }

    public function exception($e, $stopPropagation) {
        if($e instanceof CustomException) {
            $this->hooks[] = 'exceptioncomponent';
            $stopPropagation();
        }
    }

    public function render()
    {
        $this->hooks[] = 'rendercomponent';

        return view('show-name', ['name' => $this->foo]);
    }

    public function testExceptionInterceptor()
    {
        throw new CustomException;
    }
}
