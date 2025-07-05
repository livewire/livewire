<?php

namespace Livewire\V4\Paginators;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use Sushi\Sushi;
use Tests\BrowserTestCase;

class SupportPaginatorsBrowserTest extends BrowserTestCase
{
    public function test_wire_paginator_as_a_property_forwards_calls_onto_the_default_paginator_object()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            #[Computed]
            public function users()
            {
                return PaginatorsUser::paginate(2);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="users">
                        @foreach ($this->users as $user)
                            <div>{{ $user->name }}</div>
                        @endforeach
                    </div>

                    <button dusk="previous-page" x-on:click="$wire.paginator.previousPage()">Previous Page</button>
                    <button dusk="next-page" x-on:click="$wire.paginator.nextPage()">Next Page</button>
                    <button dusk="set-page-3" x-on:click="$wire.paginator.setPage(3)">Set Page 3</button>
                    <button dusk="reset-page" x-on:click="$wire.paginator.resetPage()">Reset Page</button>
                    <p dusk="current-page" x-text="$wire.paginator.currentPage()"></p>
                    <p dusk="first-page" x-text="$wire.paginator.firstPage()"></p>
                    <p dusk="last-page" x-text="$wire.paginator.lastPage()"></p>
                    <p dusk="has-pages" x-text="$wire.paginator.hasPages()"></p>
                    <p dusk="has-more-pages" x-text="$wire.paginator.hasMorePages()"></p>
                    <p dusk="has-previous-page" x-text="$wire.paginator.hasPreviousPage()"></p>
                    <p dusk="has-next-page" x-text="$wire.paginator.hasNextPage()"></p>
                    <p dusk="per-page" x-text="$wire.paginator.perPage()"></p>
                    <p dusk="count" x-text="$wire.paginator.count()"></p>
                    <p dusk="total" x-text="$wire.paginator.total()"></p>
                    <p dusk="on-first-page" x-text="$wire.paginator.onFirstPage()"></p>
                    <p dusk="on-last-page" x-text="$wire.paginator.onLastPage()"></p>
                    <p dusk="first-item" x-text="$wire.paginator.firstItem()"></p>
                    <p dusk="last-item" x-text="$wire.paginator.lastItem()"></p>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@first-page', '1')
            ->assertSeeIn('@last-page', '3')
            ->assertSeeIn('@has-pages', 'true')
            ->assertSeeIn('@per-page', '2')
            ->assertSeeIn('@count', '2')
            ->assertSeeIn('@total', '6')

            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'true')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '1')
            ->assertSeeIn('@last-item', '2')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '3')
            ->assertSeeIn('@last-item', '4')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'true')
            ->assertSeeIn('@first-item', '5')
            ->assertSeeIn('@last-item', '6')

            ->waitForLivewire()->click('@previous-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@set-page-3')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')

            ->waitForLivewire()->click('@reset-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ;
    }

    public function test_wire_paginator_as_a_function_returns_the_default_paginator_object()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            #[Computed]
            public function users()
            {
                return PaginatorsUser::paginate(2);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="users">
                        @foreach ($this->users as $user)
                            <div>{{ $user->name }}</div>
                        @endforeach
                    </div>

                    <button dusk="previous-page" x-on:click="$wire.paginator().previousPage()">Previous Page</button>
                    <button dusk="next-page" x-on:click="$wire.paginator().nextPage()">Next Page</button>
                    <button dusk="set-page-3" x-on:click="$wire.paginator().setPage(3)">Set Page 3</button>
                    <button dusk="reset-page" x-on:click="$wire.paginator().resetPage()">Reset Page</button>
                    <p dusk="current-page" x-text="$wire.paginator().currentPage()"></p>
                    <p dusk="first-page" x-text="$wire.paginator().firstPage()"></p>
                    <p dusk="last-page" x-text="$wire.paginator().lastPage()"></p>
                    <p dusk="has-pages" x-text="$wire.paginator().hasPages()"></p>
                    <p dusk="has-more-pages" x-text="$wire.paginator().hasMorePages()"></p>
                    <p dusk="has-previous-page" x-text="$wire.paginator().hasPreviousPage()"></p>
                    <p dusk="has-next-page" x-text="$wire.paginator().hasNextPage()"></p>
                    <p dusk="per-page" x-text="$wire.paginator().perPage()"></p>
                    <p dusk="count" x-text="$wire.paginator().count()"></p>
                    <p dusk="total" x-text="$wire.paginator().total()"></p>
                    <p dusk="on-first-page" x-text="$wire.paginator().onFirstPage()"></p>
                    <p dusk="on-last-page" x-text="$wire.paginator().onLastPage()"></p>
                    <p dusk="first-item" x-text="$wire.paginator().firstItem()"></p>
                    <p dusk="last-item" x-text="$wire.paginator().lastItem()"></p>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@first-page', '1')
            ->assertSeeIn('@last-page', '3')
            ->assertSeeIn('@has-pages', 'true')
            ->assertSeeIn('@per-page', '2')
            ->assertSeeIn('@count', '2')
            ->assertSeeIn('@total', '6')

            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'true')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '1')
            ->assertSeeIn('@last-item', '2')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '3')
            ->assertSeeIn('@last-item', '4')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'true')
            ->assertSeeIn('@first-item', '5')
            ->assertSeeIn('@last-item', '6')

            ->waitForLivewire()->click('@previous-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@set-page-3')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')

            ->waitForLivewire()->click('@reset-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ;
    }

    public function test_wire_paginator_as_a_function_returns_a_named_paginator_object()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            #[Computed]
            public function users()
            {
                return PaginatorsUser::paginate(2, pageName: 'users');
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="users">
                        @foreach ($this->users as $user)
                            <div>{{ $user->name }}</div>
                        @endforeach
                    </div>

                    <button dusk="previous-page" x-on:click="$wire.paginator('users').previousPage()">Previous Page</button>
                    <button dusk="next-page" x-on:click="$wire.paginator('users').nextPage()">Next Page</button>
                    <button dusk="set-page-3" x-on:click="$wire.paginator('users').setPage(3)">Set Page 3</button>
                    <button dusk="reset-page" x-on:click="$wire.paginator('users').resetPage()">Reset Page</button>
                    <p dusk="current-page" x-text="$wire.paginator('users').currentPage()"></p>
                    <p dusk="first-page" x-text="$wire.paginator('users').firstPage()"></p>
                    <p dusk="last-page" x-text="$wire.paginator('users').lastPage()"></p>
                    <p dusk="has-pages" x-text="$wire.paginator('users').hasPages()"></p>
                    <p dusk="has-more-pages" x-text="$wire.paginator('users').hasMorePages()"></p>
                    <p dusk="has-previous-page" x-text="$wire.paginator('users').hasPreviousPage()"></p>
                    <p dusk="has-next-page" x-text="$wire.paginator('users').hasNextPage()"></p>
                    <p dusk="per-page" x-text="$wire.paginator('users').perPage()"></p>
                    <p dusk="count" x-text="$wire.paginator('users').count()"></p>
                    <p dusk="total" x-text="$wire.paginator('users').total()"></p>
                    <p dusk="on-first-page" x-text="$wire.paginator('users').onFirstPage()"></p>
                    <p dusk="on-last-page" x-text="$wire.paginator('users').onLastPage()"></p>
                    <p dusk="first-item" x-text="$wire.paginator('users').firstItem()"></p>
                    <p dusk="last-item" x-text="$wire.paginator('users').lastItem()"></p>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@first-page', '1')
            ->assertSeeIn('@last-page', '3')
            ->assertSeeIn('@has-pages', 'true')
            ->assertSeeIn('@per-page', '2')
            ->assertSeeIn('@count', '2')
            ->assertSeeIn('@total', '6')

            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'true')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '1')
            ->assertSeeIn('@last-item', '2')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '3')
            ->assertSeeIn('@last-item', '4')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'true')
            ->assertSeeIn('@first-item', '5')
            ->assertSeeIn('@last-item', '6')

            ->waitForLivewire()->click('@previous-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@set-page-3')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')

            ->waitForLivewire()->click('@reset-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ;
    }

    public function test_wire_paginator_works_with_a_simple_paginator()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            #[Computed]
            public function users()
            {
                return PaginatorsUser::simplePaginate(2);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="users">
                        @foreach ($this->users as $user)
                            <div>{{ $user->name }}</div>
                        @endforeach
                    </div>

                    <button dusk="previous-page" x-on:click="$wire.paginator().previousPage()">Previous Page</button>
                    <button dusk="next-page" x-on:click="$wire.paginator().nextPage()">Next Page</button>
                    <button dusk="set-page-3" x-on:click="$wire.paginator().setPage(3)">Set Page 3</button>
                    <button dusk="reset-page" x-on:click="$wire.paginator().resetPage()">Reset Page</button>
                    <p dusk="current-page" x-text="$wire.paginator().currentPage()"></p>
                    <p dusk="has-pages" x-text="$wire.paginator().hasPages()"></p>
                    <p dusk="has-previous-page" x-text="$wire.paginator().hasPreviousPage()"></p>
                    <p dusk="has-next-page" x-text="$wire.paginator().hasNextPage()"></p>
                    <p dusk="per-page" x-text="$wire.paginator().perPage()"></p>
                    <p dusk="count" x-text="$wire.paginator().count()"></p>
                    <p dusk="on-first-page" x-text="$wire.paginator().onFirstPage()"></p>
                    <p dusk="on-last-page" x-text="$wire.paginator().onLastPage()"></p>
                    <p dusk="first-item" x-text="$wire.paginator().firstItem()"></p>
                    <p dusk="last-item" x-text="$wire.paginator().lastItem()"></p>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@has-pages', 'true')
            ->assertSeeIn('@per-page', '2')
            ->assertSeeIn('@count', '2')

            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'true')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '1')
            ->assertSeeIn('@last-item', '2')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'false')
            ->assertSeeIn('@first-item', '3')
            ->assertSeeIn('@last-item', '4')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'true')
            ->assertSeeIn('@first-item', '5')
            ->assertSeeIn('@last-item', '6')

            ->waitForLivewire()->click('@previous-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '2')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@set-page-3')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '3')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')

            ->waitForLivewire()->click('@reset-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeIn('@current-page', '1')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ;
    }

    public function test_wire_paginator_works_with_a_cursor_paginator()
    {
        Livewire::visit(new class extends Component {
            use WithPagination;

            #[Computed]
            public function users()
            {
                return PaginatorsUser::cursorPaginate(2);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="users">
                        @foreach ($this->users as $user)
                            <div>{{ $user->name }}</div>
                        @endforeach
                    </div>

                    <button dusk="previous-page" x-on:click="$wire.paginator('cursor').previousPage()">Previous Page</button>
                    <button dusk="next-page" x-on:click="$wire.paginator('cursor').nextPage()">Next Page</button>
                    <button dusk="set-cursor-page-3" x-on:click="$wire.paginator('cursor').setPage('eyJwYWdpbmF0b3JzX3VzZXJzLmlkIjo0LCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9')">Set Cursor Page 3</button>
                    <p dusk="current-cursor" x-text="$wire.paginator('cursor').currentCursor()"></p>
                    <p dusk="has-pages" x-text="$wire.paginator('cursor').hasPages()"></p>
                    <p dusk="has-more-pages" x-text="$wire.paginator('cursor').hasMorePages()"></p>
                    <p dusk="has-previous-page" x-text="$wire.paginator('cursor').hasPreviousPage()"></p>
                    <p dusk="has-next-page" x-text="$wire.paginator('cursor').hasNextPage()"></p>
                    <p dusk="per-page" x-text="$wire.paginator('cursor').perPage()"></p>
                    <p dusk="count" x-text="$wire.paginator('cursor').count()"></p>
                    <p dusk="on-first-page" x-text="$wire.paginator('cursor').onFirstPage()"></p>
                    <p dusk="on-last-page" x-text="$wire.paginator('cursor').onLastPage()"></p>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            // ->tinker()
            ->assertSeeIn('@has-pages', 'true')
            ->assertSeeIn('@per-page', '2')
            ->assertSeeIn('@count', '2')

            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeNothingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'true')
            ->assertSeeIn('@on-last-page', 'false')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeAnythingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'false')

            ->waitForLivewire()->click('@next-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeAnythingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ->assertSeeIn('@on-first-page', 'false')
            ->assertSeeIn('@on-last-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertSeeIn('@users', 'John Smith')
            ->assertSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeAnythingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@previous-page')
            ->assertSeeIn('@users', 'John Doe')
            ->assertSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertDontSeeIn('@users', 'Bob Smith')
            ->assertDontSeeIn('@users', 'Alice Smith')
            ->assertSeeAnythingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'true')
            ->assertSeeIn('@has-previous-page', 'false')
            ->assertSeeIn('@has-next-page', 'true')

            ->waitForLivewire()->click('@set-cursor-page-3')
            ->assertDontSeeIn('@users', 'John Doe')
            ->assertDontSeeIn('@users', 'Jane Doe')
            ->assertDontSeeIn('@users', 'John Smith')
            ->assertDontSeeIn('@users', 'Jane Smith')
            ->assertSeeIn('@users', 'Bob Smith')
            ->assertSeeIn('@users', 'Alice Smith')
            ->assertSeeAnythingIn('@current-cursor')
            ->assertSeeIn('@has-more-pages', 'false')
            ->assertSeeIn('@has-previous-page', 'true')
            ->assertSeeIn('@has-next-page', 'false')
            ;
    }
}

class PaginatorsUser extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'John Doe'],
        ['id' => 2, 'name' => 'Jane Doe'],
        ['id' => 3, 'name' => 'John Smith'],
        ['id' => 4, 'name' => 'Jane Smith'],
        ['id' => 5, 'name' => 'Bob Smith'],
        ['id' => 6, 'name' => 'Alice Smith'],
    ];
}
