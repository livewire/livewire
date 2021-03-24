<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class ComponentCustomPaginationTest extends TestCase
{
    /** @test */
    public function can_navigate_to_previous_page()
    {
        Livewire::test(ComponentWithCustomPaginationStub::class)
            ->set('customPage', 2)
            ->call('previousPage')
            ->assertSet('customPage', 1);
    }

    /** @test */
    public function can_navigate_to_next_page()
    {
        Livewire::test(ComponentWithCustomPaginationStub::class)
            ->call('nextPage')
            ->assertSet('customPage', 2);
    }

    /** @test */
    public function can_navigate_to_specific_page()
    {
        Livewire::test(ComponentWithCustomPaginationStub::class)
            ->call('gotoPage', 5)
            ->assertSet('customPage', 5);
    }

    /** @test */
    public function previous_page_cannot_be_less_than_one()
    {
        Livewire::test(ComponentWithCustomPaginationStub::class)
            ->call('previousPage')
            ->assertSet('customPage', 1);
    }
}

class ComponentWithCustomPaginationStub extends Component
{
    use WithPagination;

    public $customPage = 1;

    public function render()
    {
        return view('show-name', ['name' => 'example']);
    }

    public function getPageName()
    {
        return 'customPage';
    }
}
