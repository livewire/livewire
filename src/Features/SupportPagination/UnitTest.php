<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use Sushi\Sushi;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_can_navigate_to_previous_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->set('paginators.page', 2)
            ->call('previousPage')
            ->assertSetStrict('paginators.page', 1);
    }

    public function test_can_navigate_to_next_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('nextPage')
            ->assertSetStrict('paginators.page', 2);
    }

    public function test_can_navigate_to_specific_page()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('gotoPage', 5)
            ->assertSetStrict('paginators.page', 5);
    }

    public function test_previous_page_cannot_be_less_than_one()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('previousPage')
            ->assertSetStrict('paginators.page', 1);
    }

    public function test_double_page_value_should_be_casted_to_int()
    {
        Livewire::test(ComponentWithPaginationStub::class)
            ->call('gotoPage', 2.5)
            ->assertSetStrict('paginators.page', 2);
    }

    public function test_can_set_a_custom_links_theme_in_component()
    {
        Livewire::test(new class extends Component {
            use WithPagination;

            function paginationView()
            {
                return 'custom-pagination-theme';
            }

            #[Computed]
            function posts()
            {
                return PaginatorPostTestModel::paginate();
            }

            function render()
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

    public function test_can_set_a_custom_simple_links_theme_in_component()
    {
        Livewire::test(new class extends Component {
            use WithPagination;

            function paginationSimpleView()
            {
                return 'custom-simple-pagination-theme';
            }

            #[Computed]
            function posts()
            {
                return PaginatorPostTestModel::simplePaginate();
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    @foreach ($this->posts as $post)
                    @endforeach

                    {{ $this->posts->links() }}
                </div>
                HTML;
            }
        })->assertSee('Custom simple pagination theme');
    }

    public function test_invalid_page_query_string_falls_back_to_first_page()
    {
        $cases = [
            '12123123123213123123213', // overflows PHP_INT_MAX, would throw on PHP 8.4 cast
            'not-a-number',
            '0',
            '-3',
        ];

        foreach ($cases as $value) {
            Livewire::withQueryParams(['page' => $value])
                ->test(ComponentExposingResolvedPageStub::class)
                ->assertSetStrict('resolvedPage', 1)
                ->assertSetStrict('pageFromGetter', 1)
                ->assertSetStrict('paginators.page', 1);
        }
    }

    public function test_valid_page_query_string_resolves_to_int()
    {
        Livewire::withQueryParams(['page' => '3'])
            ->test(ComponentExposingResolvedPageStub::class)
            ->assertSetStrict('resolvedPage', 3)
            ->assertSetStrict('pageFromGetter', 3)
            ->assertSetStrict('paginators.page', 3);
    }

    public function test_calling_pagination_getPage_before_paginate_method_resolve_the_correct_page_number_in_first_visit_or_after_reload()
    {
        Livewire::withQueryParams(['page' => 5])->test(new class extends Component {
            use WithPagination;

            public int $page = 1;

            #[Computed]
            function posts()
            {
                $this->page = $this->getPage();
                return PaginatorPostTestModel::paginate();
            }

            function render()
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
            ->assertSetStrict('page', 5)
            ->assertSetStrict('paginators.page', 5)
            ->call('gotoPage', 3)
            ->assertSetStrict('page', 3)
            ->assertSetStrict('paginators.page', 3);
    }
}

class ComponentWithPaginationStub extends TestComponent
{
    use WithPagination;
}

class ComponentExposingResolvedPageStub extends Component
{
    use WithPagination;

    public int $resolvedPage = 0;

    public $pageFromGetter = null;

    #[Computed]
    function posts()
    {
        $paginator = PaginatorPostTestModel::paginate();
        $this->resolvedPage = $paginator->currentPage();
        $this->pageFromGetter = $this->getPage();

        return $paginator;
    }

    function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($this->posts as $post)
            @endforeach
        </div>
        HTML;
    }
}

class PaginatorPostTestModel extends Model
{
    use Sushi;

    protected $rows = [];
}
