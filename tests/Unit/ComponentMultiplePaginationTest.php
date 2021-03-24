<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class ComponentMultiplePaginationTest extends TestCase
{
    /** @test */
    public function can_navigate_to_previous_page_on_both_pagination()
    {
        Livewire::test(ComponentWithMultiplePaginationStub::class)
            ->set(['threadsListPage' => 2, 'messagesListPage' => 2])
            ->call('previousPage','threadsListPage')
            ->assertSet('threadsListPage', 1)

            ->call('previousPage','messagesListPage')
            ->assertSet('messagesListPage', 1);
    }

    /** @test */
    public function can_navigate_to_next_page_on_both_pagination()
    {
        Livewire::test(ComponentWithMultiplePaginationStub::class)
            ->call('nextPage', 'threadsListPage')
            ->assertSet('threadsListPage', 2)

            ->call('nextPage','messagesListPage')
            ->assertSet('messagesListPage', 2);
    }

    /** @test */
    public function can_navigate_to_specific_page_on_both_pagination()
    {
        Livewire::test(ComponentWithMultiplePaginationStub::class)
            ->call('gotoPage',3,'threadsListPage')
            ->assertSet('threadsListPage', 3)

            ->call('gotoPage',5, 'messagesListPage')
            ->assertSet('messagesListPage', 5);
    }

    /** @test */
    public function previous_page_cannot_be_less_than_one_on_both_pagination()
    {
        Livewire::test(ComponentWithMultiplePaginationStub::class)
            ->call('previousPage', 'threadsListPage')
            ->assertSet('threadsListPage', 1)

            ->call('previousPage', 'messagesListPage')
            ->assertSet('messagesListPage', 1);
    }

    /** @test */
    public function paginators_are_not_in_conflict()
    {
        Livewire::test(ComponentWithMultiplePaginationStub::class)
            ->call('gotoPage',5,'threadsListPage')
            ->assertSet('threadsListPage', 5)
            ->assertSet('messagesListPage', 1)

            ->call('gotoPage',3, 'messagesListPage')
            ->assertSet('messagesListPage', 3)
            ->assertSet('threadsListPage', 5);
    }
}

class ComponentWithMultiplePaginationStub extends Component
{
    use WithPagination;

    public $threadsListPage = 1;
    public $messagesListPage = 1;

    protected $paginatorNames = ['threadsListPage', 'messagesListPage'];

    public function render()
    {
        return view('show-name', ['name' => 'example']);
    }
}
