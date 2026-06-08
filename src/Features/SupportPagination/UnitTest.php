<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Cursor;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('cursorPaginationThemeProvider')]
    public function test_cursor_pagination_does_not_crash_when_a_forward_cursor_lands_on_an_empty_page($theme)
    {
        // A forward cursor pointing past the available data (e.g. a stale/bookmarked
        // cursor, or records removed since) resolves to an empty page that is not the
        // first page. `previousCursor()` is then null, which previously crashed the
        // view via `previousCursor()->encode()`. The previous link should instead fall
        // back to the current cursor (reloading the same page, mirroring Laravel).
        $cursor = (new Cursor(['id' => 3], true))->encode();

        Livewire::withQueryParams(['page' => $cursor])
            ->test(new CursorPaginationStub($theme))
            ->assertSuccessful()
            ->assertSee($cursor);
    }

    #[DataProvider('cursorPaginationThemeProvider')]
    public function test_cursor_pagination_does_not_crash_when_a_backward_cursor_lands_on_an_empty_page($theme)
    {
        // A backward cursor (clicking "previous") landing on an empty page reports
        // `hasMorePages()` as true while `nextCursor()` is null, which previously
        // crashed the view via `nextCursor()->encode()`. The next link should instead
        // fall back to the current cursor.
        $cursor = (new Cursor(['id' => 1], false))->encode();

        Livewire::withQueryParams(['page' => $cursor])
            ->test(new CursorPaginationStub($theme))
            ->assertSuccessful()
            ->assertSee($cursor);
    }

    public static function cursorPaginationThemeProvider()
    {
        return [
            'tailwind' => ['tailwind'],
            'bootstrap' => ['bootstrap'],
        ];
    }
}

class ComponentWithPaginationStub extends TestComponent
{
    use WithPagination;
}

class CursorPaginationStub extends Component
{
    use WithPagination;

    public string $theme;

    public function __construct($theme = 'tailwind')
    {
        $this->theme = $theme;
    }

    public function paginationSimpleView()
    {
        return 'livewire::simple-'.$this->theme;
    }

    #[Computed]
    public function posts()
    {
        return CursorPaginatorPostTestModel::orderBy('id')->cursorPaginate(3, ['*'], 'page');
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($this->posts as $post)
                <span wire:key="post-{{ $post->id }}">{{ $post->title }}</span>
            @endforeach

            {{ $this->posts->links() }}
        </div>
        HTML;
    }
}

class CursorPaginatorPostTestModel extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'Post #1'],
        ['title' => 'Post #2'],
        ['title' => 'Post #3'],
    ];
}

class PaginatorPostTestModel extends Model
{
    use Sushi;

    protected $rows = [];
}
