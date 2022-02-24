<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class ComponentTraitsTest extends TestCase
{
    /** @test */
    public function traits_can_intercept_lifecycle_hooks()
    {
        Livewire::test(ComponentWithTraitStub::class)
            ->assertSet(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'mount', 'rendering', 'rendered:show-name', 'dehydrate']
            )
            ->set('foo', 'bar')
            ->assertSet(
                'hooksFromTrait',
                ['initialized', 'hydrate', 'updating:foobar', 'updated:foobar', 'rendering', 'rendered:show-name', 'dehydrate']
            );
    }

    /** @test */
    public function multiple_traits_can_intercept_lifecycle_hooks()
    {
        Livewire::test(ComponentWithTwoTraitsStub::class)
            ->assertSet('hooksFromTrait', [
                'initialized', 'secondInitialized', 
                'hydrate', 'secondHydrate', 
                'mount', 'secondMount',
                'rendering', 'secondRendering',
                'rendered:show-name', 'secondRendered:show-name', 
                'dehydrate', 'secondDehydrate'
            ])
            ->set('foo', 'bar')
            ->assertSet('hooksFromTrait', [
                'initialized', 'secondInitialized', 
                'hydrate', 'secondHydrate', 
                'updating:foobar', 'secondUpdating:foobar', 
                'updated:foobar', 'secondUpdated:foobar', 
                'rendering', 'secondRendering', 
                'rendered:show-name', 'secondRendered:show-name', 
                'dehydrate', 'secondDehydrate'
            ]);
    }

    /** @test */
    public function calling_test_methods_will_not_run_hooks_from_previous_methods()
    {
        ComponentForTestMethodsStub::$hooksFromTrait = [];
        $test = Livewire::test(ComponentForTestMethodsStub::class);
        $this->assertEquals(
            ['initialized', 'hydrate', 'mount', 'rendering', 'rendered:show-name', 'dehydrate',], 
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
}

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
}

class ComponentWithTraitStub extends Component
{
    use TraitForComponent;

    public $hooksFromTrait = [];

    public $foo = 'bar';

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
