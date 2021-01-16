<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class ComponentPaginationTest extends TestCase
{
    /** @test */
    public function can_navigate_to_previous_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->set('page', 2)
            ->call('previousPage')
            ->assertSet('page', 1);
    }

    /** @test */
    public function can_navigate_to_next_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('nextPage')
            ->assertSet('page', 2);
    }

    /** @test */
    public function can_navigate_to_specific_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('gotoPage', 5)
            ->assertSet('page', 5);
    }

    /** @test */
    public function previous_page_cannot_be_less_than_one()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('previousPage')
            ->assertSet('page', 1);
    }

    /** @test */
    public function resolve_default_page_number_if_request_has_wrong_page_parameter()
    {
        Livewire::withQueryParams(['page' => '2/foo'])
            ->test(ComponentWithPaginationStub::class)
            ->assertSet('page', 1);
    }

    /** @test */
    public function resolve_integer_page_number_from_query()
    {
        Livewire::withQueryParams(['page' => 43])
            ->test(ComponentWithPaginationStub::class)
            ->call('initializeWithPagination')
            ->assertSet('page', 43);
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
