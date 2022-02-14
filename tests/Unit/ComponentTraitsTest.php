<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class ComponentTraitsTest extends TestCase
{
    public function setUp(): void
    {
        Counter::reset();
        parent::setUp();
    }

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
    public function hooks_are_not_executed_more_than_they_should()
    {
        Livewire::test(ComponentWithTraitStub::class)
            ->set('foo', 'bar');

        $this->assertEquals(1, Counter::$counts['mount']);
        $this->assertEquals(2, Counter::$counts['hydrate']);
        $this->assertEquals(2, Counter::$counts['dehydrate']);
        $this->assertEquals(1, Counter::$counts['updating']);
        $this->assertEquals(1, Counter::$counts['updated']);
        $this->assertEquals(2, Counter::$counts['rendering']);
        $this->assertEquals(2, Counter::$counts['rendered']);
    }
}

class Counter
{
    public static $counts = [];

    public static function reset()
    {
        $hooks = ['mount', 'hydrate', 'dehydrate', 'updating', 'updated', 'rendering', 'rendered'];
        static::$counts = collect($hooks)->mapWithKeys(fn ($hook) => [$hook => 0])->all();
    }
}

trait TraitForComponent
{
    public function mountTraitForComponent()
    {
        Counter::$counts['mount']++;
        $this->hooksFromTrait[] = 'mount';
    }

    public function hydrateTraitForComponent()
    {
        Counter::$counts['hydrate']++;
        $this->hooksFromTrait[] = 'hydrate';
    }

    public function dehydrateTraitForComponent()
    {
        Counter::$counts['dehydrate']++;
        $this->hooksFromTrait[] = 'dehydrate';
    }

    public function updatingTraitForComponent($name, $value)
    {
        Counter::$counts['updating']++;
        $this->hooksFromTrait[] = 'updating:'.$name.$value;
    }

    public function updatedTraitForComponent($name, $value)
    {
        Counter::$counts['updated']++;
        $this->hooksFromTrait[] = 'updated:'.$name.$value;
    }

    public function renderingTraitForComponent()
    {
        Counter::$counts['rendering']++;
        $this->hooksFromTrait[] = 'rendering';
    }

    public function renderedTraitForComponent($view)
    {
        Counter::$counts['rendered']++;
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
