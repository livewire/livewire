<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class ComponentPaginationTest extends \Tests\TestCase
{
    /** @test */
    public function can_navigate_to_previous_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->set('paginators.page', 2)
            ->call('previousPage')
            ->assertSet('paginators.page', 1);
    }

    /** @test */
    public function can_navigate_to_next_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('nextPage')
            ->assertSet('paginators.page', 2);
    }

    /** @test */
    public function can_navigate_to_specific_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('gotoPage', 5)
            ->assertSet('paginators.page', 5);
    }

    /** @test */
    public function previous_page_cannot_be_less_than_one()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('previousPage')
            ->assertSet('paginators.page', 1);
    }

    /** @test */
    public function double_page_value_should_be_casted_to_int()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('gotoPage', 2.5)
            ->assertSet('paginators.page', 2);
    }
}

class ComponentWithPaginationStub extends Component
{
    use WithPagination;

    public function render()
    {
        return view('show-name', ['name' => 'example']);
    }
}
