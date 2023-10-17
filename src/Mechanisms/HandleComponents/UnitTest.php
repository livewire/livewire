<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function it_restores_laravel_middleware_after_livewire_test()
    {
        // Run a basic Livewire test first to ensure Livewire has disabled
        // trim strings and convert empty strings to null middleware
        Livewire::test(BasicComponent::class)
            ->set('name', 'test')
            ->assertSet('name', 'test');

        // Then make a standard laravel test and ensure that the input has
        // had trim strings re-applied
        Route::post('laravel', function () {
            return 'laravel'.request()->input('name').'laravel';
        });

        $this->post('laravel', ['name' => '    aaa    '])
            ->assertSee('laravelaaalaravel');
    }

    /** @test */
    public function synthesized_property_types_are_preserved_after_update()
    {
        Livewire::test(new class extends Component
        {
            public $foo;

            public $isStringable;

            public function mount()
            {
                $this->foo = str('bar');
            }

            public function checkStringable()
            {
                $this->isStringable = $this->foo instanceof Stringable;
            }

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('foo', 'bar')
            ->call('checkStringable')
            ->assertSet('isStringable', true)
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz')
            ->call('checkStringable')
            ->assertSet('isStringable', true);
    }

    /** @test */
    public function uninitialized_integer_can_be_set_to_empty_string()
    {
        Livewire::test(new class extends Component
        {
            public int $count;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <h1 dusk="count">count: {{ $count }};</h1>
                    </div>
                HTML;
            }
        })
            ->assertSee('count: ;')
            ->set('count', 1)
            ->assertSee('count: 1;')
            ->set('count', '')
            ->assertSee('count: ;');
    }

    /** @test */
    public function uninitialized_integer_in_a_form_object_can_be_set_to_empty_string()
    {
        Livewire::test(new class extends Component
        {
            public CountForm $form;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <!--  -->
                    </div>
                HTML;
            }
        })
            ->assertSet('form.count', null)
            ->set('form.count', 1)
            ->assertSet('form.count', 1)
            ->set('form.count', '')
            ->assertSet('form.count', null);
    }

    /** @test */
    public function it_uses_the_synthesizers_for_enum_property_updates_when_initial_state_is_null()
    {
        Livewire::test(new class extends \Livewire\Component
        {
            public ?UnitSuit $selected;

            #[\Livewire\Attributes\Computed]
            public function cases()
            {
                return UnitSuit::cases();
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <select wire:model.live="selected" dusk="selectInput">
                        @foreach($this->cases() as $suit)
                            <option value="{{ $suit->value }}">{{ $suit }}</option>
                        @endforeach
                    </select>

                    <span dusk="selected">{{ $selected }}</span>
                </div>
                HTML;
            }
        })
            ->assertSet('selected', null)
            ->set('selected', 'D')
            ->assertSet('selected', UnitSuit::Diamonds)
            ->set('selected', null)
            ->assertSet('selected', null);
    }

    /** @test */
    public function it_uses_the_synthesizers_for_enum_property_updates_when_initial_state_is_null_inside_form_object()
    {
        Livewire::test(new class extends \Livewire\Component
        {
            public SuitForm $form;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <!--  -->
                </div>
                HTML;
            }
        })
            ->assertSet('form.selected', null)
            ->set('form.selected', 'D')
            ->assertSet('form.selected', UnitSuit::Diamonds)
            ->set('form.selected', null)
            ->assertSet('form.selected', null);
    }
}

class BasicComponent extends Component
{
    public $name;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithStringPropertiesStub extends Component
{
    public $emptyString = '';

    public $oneSpace = ' ';

    public function render()
    {
        return app('view')->make('null-view');
    }
}

enum UnitSuit: string
{
    case Hearts = 'H';

    case Diamonds = 'D';

    case Clubs = 'C';

    case Spades = 'S';
}

class CountForm extends Form
{
    public int $count;
}

class SuitForm extends Form
{
    public UnitSuit $selected;
}
