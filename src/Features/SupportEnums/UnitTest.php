<?php

namespace Livewire\Features\SupportEnums;

use Illuminate\Contracts\Support\DeferringDisplayableValue;
use Tests\TestComponent;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_use_a_backed_enum_as_a_property()
    {
        Livewire::test(new class extends TestComponent {
            public Country $country;

            public function mount()
            {
                $this->country = Country::United_States;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->value }}</div>
                HTML;
            }
        })
        ->assertSee('US')
        ->call('$refresh')
        ->assertSee('US')
        ->set('country', 'CA')
        ->assertSee('CA')
        ;
    }

    /** @test */
    public function can_set_an_enum_as_the_initial_value()
    {
        Livewire::test(new class extends TestComponent {
            public Country $country = Country::United_States;

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->value }}</div>
                HTML;
            }
        })
        ->assertSee('US')
        ->call('$refresh')
        ->assertSee('US')
        ->set('country', 'CA')
        ->assertSee('CA')
        ;
    }

    /** @test */
    public function can_make_an_enum_displayable()
    {
        Livewire::test(new class extends TestComponent {
            public Country $country;

            public function mount()
            {
                $this->country = Country::United_States;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->display() }}</div>
                HTML;
            }
        })
        ->assertSee('United States')
        ->set('country', 'CA')
        ->assertSee('O Canada')
        ;
    }

    /** @test */
    public function can_customize_display_strategy()
    {
        Livewire::test(new class extends TestComponent {
            public PullUpMethodCountry $country;

            public function mount()
            {
                $this->country = PullUpMethodCountry::United_States;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->display() }}</div>
                HTML;
            }
        })
        ->assertSee('foo')
        ;
    }

    /** @test */
    public function can_use_laravel_displayable_interface()
    {
        Livewire::test(new class extends TestComponent {
            public DisplayInterfaceCountry $country;

            public function mount()
            {
                $this->country = DisplayInterfaceCountry::United_States;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country }}</div>
                HTML;
            }
        })
        ->assertSee('United States')
        ->set('country', 'CA')
        ->assertSee('O Canada')
        ;
    }
}

enum Country: string
{
    use \Livewire\Enums\Displayable;

    case United_States = 'US';

    #[\Livewire\Enums\Display('O Canada')]
    case Canada = 'CA';
}

enum PullUpMethodCountry: string
{
    use \Livewire\Enums\Displayable;

    public function display()
    {
        return 'foo';
    }

    case United_States = 'US';

    #[\Livewire\Enums\Display('O Canada')]
    case Canada = 'CA';
}

enum DisplayInterfaceCountry: string implements \Illuminate\Contracts\Support\DeferringDisplayableValue
{
    use \Livewire\Enums\Displayable;

    case United_States = 'US';

    #[\Livewire\Enums\Display('O Canada')]
    case Canada = 'CA';
}
