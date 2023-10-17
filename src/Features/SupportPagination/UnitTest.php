<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use Sushi\Sushi;

class UnitTest extends \Tests\TestCase
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

    /** @test */
    public function can_set_a_custom_links_theme_in_component()
    {
        Livewire::test(new class extends Component
        {
            use WithPagination;

            public function paginationView()
            {
                return 'custom-pagination-theme';
            }

            #[Computed]
            public function posts()
            {
                return PaginatorPostTestModel::paginate();
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    @foreach ($this->posts as $post)
                    @endforeach

                    {{ $this->posts->links() }}
                </div>
                HTML;
            }
        })->assertSee('Custom pagination theme');
    }

    public function test_calling_pagination_getPage_before_paginate_method_resolve_the_correct_page_number_in_first_visit_or_after_reload()
    {
        Livewire::withQueryParams(['page' => 5])->test(new class extends Component
        {
            use WithPagination;

            public int $page = 1;

            #[Computed]
            public function posts()
            {
                $this->page = $this->getPage();

                return PaginatorPostTestModel::paginate();
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    @foreach ($this->posts as $post)
                    @endforeach

                    {{ $this->posts->links() }}
                </div>
                HTML;
            }
        })
            ->assertSet('page', 5)
            ->assertSet('paginators.page', 5)
            ->call('gotoPage', 3)
            ->assertSet('page', 3)
            ->assertSet('paginators.page', 3);
    }
}

class ComponentWithPaginationStub extends Component
{
    use WithPagination;

    public function render()
    {
        return '<div></div>';
    }
}

class PaginatorPostTestModel extends Model
{
    use Sushi;

    protected $rows = [];
}
