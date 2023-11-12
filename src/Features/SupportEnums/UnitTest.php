<?php

namespace Livewire\Features\SupportEnums;

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
                $this->country = Country::US;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->name }}</div>
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
    public function can_assign_a_description_to_an_enum()
    {
        Livewire::test(new class extends TestComponent {
            public Country $country;

            public function mount()
            {
                $this->country = Country::US;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $country->description() }}</div>
                HTML;
            }
        })
        ->assertSee('United States')
        ;
    }
}

enum Country: string
{
    use \Livewire\Enums\Describable;

    #[\Livewire\Enums\Description('United States')]
    case US = 'US';

    #[\Livewire\Enums\Description('Canada')]
    case CA = 'CA';
}
