<?php

namespace Livewire\Features\SupportPagination;

use Tests\BrowserTestCase;
use Sushi\Sushi;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;

class BrowserTest extends BrowserTestCase
{
    public function test_tailwind()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        {{ $posts->links() }}
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                    ]
                );
            }
        })

        // Test that going to page 2, then back to page 1 removes "page" from the query string.
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage.before')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')
        ->assertQueryStringHas('page', '2')

        ->waitForLivewire()->click('@previousPage.before')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertQueryStringMissing('page')

        // Test that using the next page button twice (the one at the end of the page numbers) works.
        ->refresh()
        ->assertSee('Post #1')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage.after')

        ->assertDontSee('Post #1')
        ->assertSee('Post #4')
        ->assertQueryStringHas('page', '2')

        ->waitForLivewire()->click('@nextPage.after')

        ->assertDontSee('Post #4')
        ->assertSee('Post #7')
        ->assertQueryStringHas('page', '3')

        // Test that hitting the back button takes you back to the previous page after a refresh.
        ->refresh()
        ->waitForLivewire()->back()
        ->assertQueryStringHas('page', '2')
        ->assertDontSee('Post #7')
        ->assertSee('Post #4')
        ;
    }

    public function test_bootstrap()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            protected $paginationTheme = 'bootstrap';

            public function render()
            {
                return Blade::render(
                    <<<'HTML'
                    <div>
                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        {{ $posts->links() }}
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                    ]
                );
            }
        })

        // Test that going to page 2, then back to page 1 removes "page" from the query string.
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #1')
        ->assertSee('Post #4')
        ->assertQueryStringHas('page', '2')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #3')
        ->assertSee('Post #7')
        ->assertSee('Post #8')
        ->assertSee('Post #9')
        ->assertQueryStringHas('page', '3')

        ->waitForLivewire()->click('@previousPage')
        ->waitForLivewire()->click('@previousPage')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertQueryStringMissing('page')
        ;
    }

    public function test_cursor_tailwind()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        {{ $posts->links() }}
                    </div>
                    HTML,
                    [
                        'posts' => Post::cursorPaginate(3, '*', 'page'),
                    ]
                );
            }
        })

        // Test it can go to second page and return to first one
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')

        ->waitForLivewire()->click('@previousPage')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')

        // Test that hitting the back button takes you back to the previous page after a refresh.
        ->refresh()
        ->assertSee('Post #1')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #1')
        ->assertSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #4')
        ->assertSee('Post #7')

        ->refresh()
        ->waitForLivewire()->back()
        ->assertDontSee('Post #7')
        ->assertSee('Post #4')
        ;
    }

    public function test_cursor_bootstrap()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            protected $paginationTheme = 'bootstrap';

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        {{ $posts->links() }}
                    </div>
                    HTML,
                    [
                        'posts' => Post::cursorPaginate(3, '*', 'page'),
                    ]
                );
            }
        })

        // Test it can go to second page and return to first one
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')

        ->waitForLivewire()->click('@previousPage')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')

        // Test that hitting the back button takes you back to the previous page after a refresh.
        ->refresh()
        ->assertSee('Post #1')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #1')
        ->assertSee('Post #4')

        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #4')
        ->assertSee('Post #7')

        ->refresh()
        ->waitForLivewire()->back()
        ->assertDontSee('Post #7')
        ->assertSee('Post #4')
        ;
    }

    public function test_it_can_have_two_sets_of_links_for_the_one_paginator_on_a_page_tailwind()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>
                            <div dusk="first-links">{{ $posts->links() }}</div>

                            @foreach ($posts as $post)
                                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                            @endforeach

                            <div dusk="second-links">{{ $posts->links() }}</div>
                        </div>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                    ]
                );
            }
        })
        // Ensure everything is good to start with
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        // Assert page 6 can be seen in both sets of links
        ->assertPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ->assertPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')

        // Click either of the page 10 links, it doesn't matter which
        ->waitForLivewire()->click('[wire\\:click="gotoPage(10, \'page\')"]')

        /*
         * Typically it is the first set of links that break due to morphdom
         * So we will test the second set of links first, to make sure everything is ok
         * Then we will check the first set of links
         */
        ->assertNotPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ->assertNotPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ;
    }

    public function test_it_can_have_two_sets_of_links_for_the_one_paginator_on_a_page_bootstrap()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            protected $paginationTheme = 'bootstrap';

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>
                            <div dusk="first-links">{{ $posts->links() }}</div>

                            @foreach ($posts as $post)
                                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                            @endforeach

                            <div dusk="second-links">{{ $posts->links() }}</div>
                        </div>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                    ]
                );
            }
        })
        // Ensure everything is good to start with
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        // Assert page 6 can be seen in both sets of links
        ->assertPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ->assertPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')

        // Click either of the page 10 links, it doesn't matter which
        ->waitForLivewire()->click('[wire\\:click="gotoPage(10, \'page\')"]')

        /*
         * Typically it is the first set of links that break due to morphdom
         * So we will test the second set of links first, to make sure everything is ok
         * Then we will check the first set of links
         */
        ->assertNotPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ->assertNotPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
        ;
    }

    public function test_it_calls_pagination_hook_method_when_pagination_changes()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public $hookOutput = null;

            public function updatedPage($page)
            {
                $this->hookOutput = 'page-is-set-to-' . $page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>

                            @foreach ($posts as $post)
                                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                            @endforeach

                            {{ $posts->links(null, ['paginatorId' => 2]) }}
                        </div>

                        <span dusk="pagination-hook">{{ $hookOutput }}</span>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                        'hookOutput' => $this->hookOutput,
                    ]
                );
            }
        })
        // Test that going to page 2, then back to page 1 removes "page" from the query string.
        ->assertSeeNothingIn('@pagination-hook')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')

        ->waitForLivewire()->click('@nextPage.before')

        ->assertSeeIn('@pagination-hook', 'page-is-set-to-2')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')
        ->assertQueryStringHas('page', '2')

        ->waitForLivewire()->click('@previousPage.before')

        ->assertSeeIn('@pagination-hook', 'page-is-set-to-1')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertQueryStringMissing('page')
        ;
    }

    public function test_it_can_have_two_pagination_instances_on_a_page_tailwind()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public $pageHookOutput = null;
            public $itemPageHookOutput = null;

            public function updatedPage($page)
            {
                $this->pageHookOutput = 'page-is-set-to-' . $page;
            }

            public function updatedItemPage($page)
            {
                $this->itemPageHookOutput = 'item-page-is-set-to-' . $page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>
                            <div>
                                @foreach ($posts as $post)
                                    <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                                @endforeach
                            </div>

                            {{ $posts->links() }}
                        </div>

                        <span dusk="page-pagination-hook">{{ $pageHookOutput }}</span>

                        <div>
                            <div>
                                @foreach ($items as $item)
                                        <h1 wire:key="item-{{ $item->id }}">{{ $item->title }}</h1>
                                @endforeach
                            </div>

                            {{ $items->links() }}
                        </div>

                        <span dusk="item-page-pagination-hook">{{ $itemPageHookOutput }}</span>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                        'items' => Item::paginate(3, ['*'], 'itemPage'),
                        'pageHookOutput' => $this->pageHookOutput,
                        'itemPageHookOutput' => $this->itemPageHookOutput,
                    ]
                );
            }
        })
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')

        // Test Posts paginator
        ->waitForLivewire()->click('@nextPage.before')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')
        ->assertQueryStringHas('page', '2')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')
        ->assertQueryStringMissing('itemPage')

        ->waitForLivewire()->click('@previousPage.before')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertQueryStringMissing('page')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')
        ->assertQueryStringMissing('itemPage')

        // Test Items paginator
        ->waitForLivewire()->click('@nextPage.itemPage.before')

        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertQueryStringMissing('page')
        ->assertDontSee('Item #3')
        ->assertSee('Item #4')
        ->assertSee('Item #5')
        ->assertSee('Item #6')
        ->assertQueryStringHas('itemPage', '2')

        ->waitForLivewire()->click('@previousPage.itemPage.before')

        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertQueryStringMissing('page')
        ->assertDontSee('Item #6')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertQueryStringMissing('itemPage')
        ;
    }

    public function test_it_can_have_two_pagination_instances_on_a_page_bootstrap()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            protected $paginationTheme = 'bootstrap';

            public $pageHookOutput = null;
            public $itemPageHookOutput = null;

            public function updatedPage($page)
            {
                $this->pageHookOutput = 'page-is-set-to-' . $page;
            }

            public function updatedItemPage($page)
            {
                $this->itemPageHookOutput = 'item-page-is-set-to-' . $page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>
                            <div>
                                @foreach ($posts as $post)
                                    <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                                @endforeach
                            </div>

                            {{ $posts->links() }}
                        </div>

                        <span dusk="page-pagination-hook">{{ $pageHookOutput }}</span>

                        <div>
                            <div>
                                @foreach ($items as $item)
                                        <h1 wire:key="item-{{ $item->id }}">{{ $item->title }}</h1>
                                @endforeach
                            </div>

                            {{ $items->links() }}
                        </div>

                        <span dusk="item-page-pagination-hook">{{ $itemPageHookOutput }}</span>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                        'items' => Item::paginate(3, ['*'], 'itemPage'),
                        'pageHookOutput' => $this->pageHookOutput,
                        'itemPageHookOutput' => $this->itemPageHookOutput,
                    ]
                );
            }
        })
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')

        // Test Posts paginator
        ->waitForLivewire()->click('@nextPage')

        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')
        ->assertQueryStringHas('page', '2')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')
        ->assertQueryStringMissing('itemPage')

        ->waitForLivewire()->click('@previousPage')

        ->assertDontSee('Post #6')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertQueryStringMissing('page')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertDontSee('Item #4')
        ->assertQueryStringMissing('itemPage')

        // Test Items paginator
        ->waitForLivewire()->click('@nextPage.itemPage')

        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertQueryStringMissing('page')
        ->assertDontSee('Item #3')
        ->assertSee('Item #4')
        ->assertSee('Item #5')
        ->assertSee('Item #6')
        ->assertQueryStringHas('itemPage', '2')

        ->waitForLivewire()->click('@previousPage.itemPage')

        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->assertQueryStringMissing('page')
        ->assertDontSee('Item #6')
        ->assertSee('Item #1')
        ->assertSee('Item #2')
        ->assertSee('Item #3')
        ->assertQueryStringMissing('itemPage')
        ;
    }

    public function test_it_calls_pagination_hook_methods_when_pagination_changes_with_multiple_paginators()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public $pageHookOutput = null;
            public $itemPageHookOutput = null;

            public function updatedPage($page)
            {
                $this->pageHookOutput = 'page-is-set-to-' . $page;
            }

            public function updatedItemPage($page)
            {
                $this->itemPageHookOutput = 'item-page-is-set-to-' . $page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div>
                            <div>
                                @foreach ($posts as $post)
                                    <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                                @endforeach
                            </div>

                            {{ $posts->links() }}
                        </div>

                        <span dusk="page-pagination-hook">{{ $pageHookOutput }}</span>

                        <div>
                            <div>
                                @foreach ($items as $item)
                                        <h1 wire:key="item-{{ $item->id }}">{{ $item->title }}</h1>
                                @endforeach
                            </div>

                            {{ $items->links() }}
                        </div>

                        <span dusk="item-page-pagination-hook">{{ $itemPageHookOutput }}</span>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                        'items' => Item::paginate(3, ['*'], 'itemPage'),
                        'pageHookOutput' => $this->pageHookOutput,
                        'itemPageHookOutput' => $this->itemPageHookOutput,
                    ]
                );
            }
        })
        ->assertSeeNothingIn('@page-pagination-hook')
        ->assertSeeNothingIn('@item-page-pagination-hook')
        ->assertSee('Post #1')
        ->assertSee('Item #1')

        ->waitForLivewire()->click('@nextPage.before')
        ->assertSeeNothingIn('@item-page-pagination-hook')
        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-2')
        ->assertSee('Post #4')
        ->assertSee('Item #1')

        ->waitForLivewire()->click('@nextPage.itemPage.before')

        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-2')
        ->assertSeeIn('@item-page-pagination-hook', 'item-page-is-set-to-2')
        ->assertSee('Post #4')
        ->assertSee('Item #4')

        ->waitForLivewire()->click('@previousPage.itemPage.before')

        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-2')
        ->assertSeeIn('@item-page-pagination-hook', 'item-page-is-set-to-1')
        ->assertSee('Post #4')
        ->assertSee('Item #1')

        ->waitForLivewire()->click('@previousPage.before')

        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-1')
        ->assertSeeIn('@item-page-pagination-hook', 'item-page-is-set-to-1')
        ->assertSee('Post #1')
        ->assertSee('Item #1')
        ;
    }

    public function test_it_calls_pagination_hook_methods_when_page_is_kebab_cased()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public $itemPageHookOutput = null;

            public function updatedItemPage($page)
            {
                $this->itemPageHookOutput = 'item-page-is-set-to-' . $page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        {{ $items->links() }}
                        <span dusk="item-page-pagination-hook">{{ $itemPageHookOutput }}</span>
                    </div>
                    HTML,
                    [
                        'items' => Item::paginate(3, ['*'], 'item-page'),
                        'itemPageHookOutput' => $this->itemPageHookOutput
                    ]
                );
            }
        })
            ->assertSeeNothingIn('@item-page-pagination-hook')
            ->waitForLivewire()->click('@nextPage.item-page.before')
            ->assertSeeIn('@item-page-pagination-hook', 'item-page-is-set-to-2');
    }

    public function test_pagination_trait_resolves_query_string_alias_for_page_from_component()
    {
        Livewire::withQueryParams(['p' => '2'])
            ->visit(new class extends Component {
                use WithPagination;

                protected $queryString = [
                    'paginators.page' => ['as' => 'p']
                ];

                public function render()
                {
                    return Blade::render(
                        <<< 'HTML'
                        <div>
                            @foreach ($posts as $post)
                                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                            @endforeach

                            {{ $posts->links() }}
                        </div>
                        HTML,
                        [
                            'posts' => Post::paginate(3),
                        ]
                    );
                }
            })

            // Test a deeplink to page 2 with "p" from the query string shows the second page.
            ->assertDontSee('Post #3')
            ->assertSee('Post #4')
            ->assertSee('Post #5')
            ->assertSee('Post #6')
            ->assertQueryStringHas('p', '2')

            ->waitForLivewire()->click('@previousPage.before')

            ->assertDontSee('Post #4')
            ->assertSee('Post #1')
            ->assertSee('Post #2')
            ->assertSee('Post #3')
            ->assertQueryStringHas('p', '1')
        ;
    }

    public function test_pagination_is_tracked_in_query_string_on_lazy_components()
    {
        Livewire::withQueryParams(['page' => '2'])
            ->visit(new #[\Livewire\Attributes\Lazy] class extends Component {
                use WithPagination;

                public function render()
                {
                    return Blade::render(
                        <<< 'HTML'
                        <div>
                            @foreach ($posts as $post)
                                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                            @endforeach

                            {{ $posts->links() }}
                        </div>
                        HTML,
                        [
                            'posts' => Post::paginate(3),
                        ]
                    );
                }
            })
            ->waitForText('Post #4')
            ->assertDontSee('Post #3')
            ->assertSee('Post #4')
            ->assertSee('Post #5')
            ->assertSee('Post #6')
            ->assertQueryStringHas('page', '2')

            ->waitForLivewire()->click('@previousPage.before')

            ->assertDontSee('Post #4')
            ->assertSee('Post #1')
            ->assertSee('Post #2')
            ->assertSee('Post #3')
            ->assertQueryStringHas('page', '1')
        ;
    }

    public function test_it_loads_pagination_on_nested_alpine_tabs()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public $pageHookOutput = null;

            public function updatedPage($page)
            {
                $this->pageHookOutput = 'page-is-set-to-'.$page;
            }

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div x-data="{ tab: window.location.hash ? window.location.hash.substring(1) : 'general' }">
                        <nav>
                            <button dusk="general" x-on:click.prevent="tab = 'general'; window.location.hash = 'general'" :class="{ 'tab--active': tab === 'general' }"
                                class="general">
                                General
                            </button>

                            <button dusk="deals" x-on:click.prevent="tab = 'deals'; window.location.hash = 'deals'"
                                :class="{ 'tab--active': tab === 'deals' }"
                                class="deals">
                                Deals
                            </button>
                        </nav>
                        <div x-show="tab === 'deals'">
                            <div x-data="{ dealSubTab: 'posts' }">
                                <nav>
                                       <button x-on:click.prevent="dealSubTab = 'posts'"
                                          :class="{ 'tab--active': dealSubTab === 'posts' }">
                                          Posts
                                     </button>
                                </nav>
                                <div x-show="dealSubTab === 'posts'">
                                    <div>
                                        @foreach ($posts as $post)
                                            <h1 wire:key='post-{{ $post->id }}'>{{ $post->title }}</h1>
                                        @endforeach
                                    </div>
                                    {{ $posts->links() }}
                                </div>
                                <span dusk="page-pagination-hook">{{ $pageHookOutput }}</span>
                            </div>
                        </div>
                    </div>
                    HTML,
                    [
                        'posts'          => Post::paginate(3),
                        'pageHookOutput' => $this->pageHookOutput,
                    ]
                );
            }
        })
        ->click('@deals')
        ->assertFragmentIs('deals')
        ->assertSee('Post #1')
        ->assertSee('Post #2')
        ->assertSee('Post #3')
        ->assertDontSee('Post #4')
        ->waitForLivewire()->click('@nextPage.before')
        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-2')
        ->assertDontSee('Post #3')
        ->assertSee('Post #4')
        ->assertSee('Post #5')
        ->assertSee('Post #6')
        ->waitForLivewire()->click('@nextPage.before')
        ->assertSeeIn('@page-pagination-hook', 'page-is-set-to-3')
        ->assertDontSee('Post #6')
        ->assertSee('Post #7')
        ->assertSee('Post #8')
        ->assertSee('Post #9');
    }

    public function test_it_loads_pagination_even_when_there_are_nested_components_that_do_not_have_pagination()
    {
        Livewire::visit([
            new class extends Component {
                use WithPagination;

                #[Computed]
                public function posts()
                {
                    return Post::paginate(3);
                }

                public function render()
                {
                    return <<<'HTML'
                <div>
                        <livewire:child />

                        @foreach ($this->posts as $post)
                        <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                    @endforeach

                    {{ $this->posts->links() }}
                </div>
                HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                        <div dusk="child">
                            Child
                        </div>
                HTML;
                }
            },
        ])
            // Test that going to page 2, then back to page 1 removes "page" from the query string.
            ->assertSee('Post #1')
            ->assertSee('Post #2')
            ->assertSee('Post #3')
            ->assertDontSee('Post #4')
            ->assertPresent('@child')
            ->assertSeeIn('@child', 'Child')

            ->waitForLivewire()->click('@nextPage.before')
            ->assertDontSee('Post #3')
            ->assertSee('Post #4')
            ->assertSee('Post #5')
            ->assertSee('Post #6')
            ->assertQueryStringHas('page', '2')
            ->assertPresent('@child')
            ->assertSeeIn('@child', 'Child')

            ->waitForLivewire()->click('@previousPage.before')

            ->assertDontSee('Post #6')
            ->assertSee('Post #1')
            ->assertSee('Post #2')
            ->assertSee('Post #3')
            ->assertQueryStringMissing('page')
            ->assertPresent('@child')
            ->assertSeeIn('@child', 'Child')
        ;
    }

    public function test_pagination_links_scroll_to_top_by_default()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        <div id="top">Top...</div>

                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        <div style="min-height: 100vh">&nbsp;</div>

                        {{ $posts->links() }}

                        <div id="bottom">Bottom...</div>
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(),
                    ]
                );
            }
        })
        ->scrollTo('#bottom')
        ->assertNotInViewPort('#top')
        ->waitForLivewire()->click('@nextPage.before')
        ->assertInViewPort('#top')
        ;
    }

    public function test_pagination_query_string_disabled()
    {
        Livewire::visit(new class extends Component {
            use WithPagination, WithoutUrlPagination;

            public function render()
            {
                return Blade::render(
                    <<< 'HTML'
                    <div>
                        @foreach ($posts as $post)
                            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
                        @endforeach

                        {{ $posts->links() }}
                    </div>
                    HTML,
                    [
                        'posts' => Post::paginate(3),
                    ]
                );
            }
        })
            ->assertSee('Post #1')
            ->assertSee('Post #2')
            ->assertSee('Post #3')
            ->assertDontSee('Post #4')

            ->waitForLivewire()->click('@nextPage.before')

            ->assertDontSee('Post #3')
            ->assertSee('Post #4')
            ->assertSee('Post #5')
            ->assertSee('Post #6')
            ->assertQueryStringMissing('page')
        ;
    }
}

class Post extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'Post #1'],
        ['title' => 'Post #2'],
        ['title' => 'Post #3'],
        ['title' => 'Post #4'],
        ['title' => 'Post #5'],
        ['title' => 'Post #6'],
        ['title' => 'Post #7'],
        ['title' => 'Post #8'],
        ['title' => 'Post #9'],
        ['title' => 'Post #10'],
        ['title' => 'Post #11'],
        ['title' => 'Post #12'],
        ['title' => 'Post #13'],
        ['title' => 'Post #14'],
        ['title' => 'Post #15'],
        ['title' => 'Post #16'],
        ['title' => 'Post #17'],
        ['title' => 'Post #18'],
        ['title' => 'Post #19'],
        ['title' => 'Post #20'],
        ['title' => 'Post #21'],
        ['title' => 'Post #22'],
        ['title' => 'Post #23'],
        ['title' => 'Post #24'],
        ['title' => 'Post #25'],
        ['title' => 'Post #26'],
        ['title' => 'Post #27'],
        ['title' => 'Post #28'],
        ['title' => 'Post #29'],
        ['title' => 'Post #30'],
        ['title' => 'Post #31'],
        ['title' => 'Post #32'],
        ['title' => 'Post #33'],
        ['title' => 'Post #34'],
        ['title' => 'Post #35'],
        ['title' => 'Post #36'],
        ['title' => 'Post #37'],
        ['title' => 'Post #38'],
        ['title' => 'Post #39'],
        ['title' => 'Post #40'],
        ['title' => 'Post #41'],
        ['title' => 'Post #42'],
        ['title' => 'Post #43'],
        ['title' => 'Post #44'],
        ['title' => 'Post #45'],
        ['title' => 'Post #46'],
        ['title' => 'Post #47'],
        ['title' => 'Post #48'],
        ['title' => 'Post #49'],
        ['title' => 'Post #50'],
        ['title' => 'Post #51'],
        ['title' => 'Post #52'],
        ['title' => 'Post #53'],
        ['title' => 'Post #54'],
        ['title' => 'Post #55'],
        ['title' => 'Post #56'],
        ['title' => 'Post #57'],
        ['title' => 'Post #58'],
        ['title' => 'Post #59'],
        ['title' => 'Post #60'],
    ];
}

class Item extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'Item #1'],
        ['title' => 'Item #2'],
        ['title' => 'Item #3'],
        ['title' => 'Item #4'],
        ['title' => 'Item #5'],
        ['title' => 'Item #6'],
        ['title' => 'Item #7'],
        ['title' => 'Item #8'],
        ['title' => 'Item #9'],
        ['title' => 'Item #10'],
        ['title' => 'Item #11'],
        ['title' => 'Item #12'],
        ['title' => 'Item #13'],
        ['title' => 'Item #14'],
        ['title' => 'Item #15'],
        ['title' => 'Item #16'],
        ['title' => 'Item #17'],
        ['title' => 'Item #18'],
        ['title' => 'Item #19'],
        ['title' => 'Item #20'],
        ['title' => 'Item #21'],
        ['title' => 'Item #22'],
        ['title' => 'Item #23'],
        ['title' => 'Item #24'],
        ['title' => 'Item #25'],
        ['title' => 'Item #26'],
        ['title' => 'Item #27'],
        ['title' => 'Item #28'],
        ['title' => 'Item #29'],
        ['title' => 'Item #30'],
        ['title' => 'Item #31'],
        ['title' => 'Item #32'],
        ['title' => 'Item #33'],
        ['title' => 'Item #34'],
        ['title' => 'Item #35'],
        ['title' => 'Item #36'],
        ['title' => 'Item #37'],
        ['title' => 'Item #38'],
        ['title' => 'Item #39'],
        ['title' => 'Item #40'],
        ['title' => 'Item #41'],
        ['title' => 'Item #42'],
        ['title' => 'Item #43'],
        ['title' => 'Item #44'],
        ['title' => 'Item #45'],
        ['title' => 'Item #46'],
        ['title' => 'Item #47'],
        ['title' => 'Item #48'],
        ['title' => 'Item #49'],
        ['title' => 'Item #50'],
        ['title' => 'Item #51'],
        ['title' => 'Item #52'],
        ['title' => 'Item #53'],
        ['title' => 'Item #54'],
        ['title' => 'Item #55'],
        ['title' => 'Item #56'],
        ['title' => 'Item #57'],
        ['title' => 'Item #58'],
        ['title' => 'Item #59'],
        ['title' => 'Item #60'],
    ];
}
