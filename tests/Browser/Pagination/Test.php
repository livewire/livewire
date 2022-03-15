<?php

namespace Tests\Browser\Pagination;

use Illuminate\Pagination\CursorPaginator;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_tailwind()
    {

        $this->browse(function ($browser) {
            Livewire::visit($browser, Tailwind::class)
                /**
                 * Test that going to page 2, then back to page 1 removes "page" from the query string.
                 */
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

                /**
                 * Test that using the next page button twice (the one at the end of the page numbers) works.
                 */
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

                /**
                 * Test that hitting the back button takes you back to the previous page after a refresh.
                 */
                ->refresh()
                ->back()
                ->assertQueryStringHas('page', '2')
                ->assertDontSee('Post #7')
                ->assertSee('Post #4')
            ;
        });
    }

    public function test_bootstrap()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Bootstrap::class)
                /**
                 * Test that going to page 2, then back to page 1 removes "page" from the query string.
                 */
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
        });
    }

    public function test_cursor_tailwind()
    {
        if (! class_exists(CursorPaginator::class)) {
            $this->markTestSkipped('Need Laravel >= 8');
        }
        $this->browse(function ($browser){
            Livewire::visit($browser,ComponentWithCursorPaginationTailwind::class)
                /**
                 * Test it can go to second page and return to first one
                 */
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

                /**
                 * Test that hitting the back button takes you back to the previous page after a refresh.
                 */
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
                ->back()
                ->assertDontSee('Post #7')
                ->assertSee('Post #4');
        });
    }
    public function test_cursor_bootstrap()
    {
        if (! class_exists(CursorPaginator::class)) {
            $this->markTestSkipped('Need Laravel >= 8');
        }
        $this->browse(function ($browser){
            Livewire::visit($browser,ComponentWithCursorPaginationBootstrap::class)
                /**
                 * Test it can go to second page and return to first one
                 */
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

                /**
                 * Test that hitting the back button takes you back to the previous page after a refresh.
                 */
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
                ->back()
                ->assertDontSee('Post #7')
                ->assertSee('Post #4');
        });
    }
    /** @test */
    public function it_can_have_two_sets_of_links_for_the_one_paginator_on_a_page()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ComponentWithTwoLinksForOnePaginator::class)
                /**
                 * Ensure everything is good to start with
                 */
                ->assertSee('Post #1')
                ->assertSee('Post #2')
                ->assertSee('Post #3')
                ->assertDontSee('Post #4')
                // Assert page 6 can be seen in both sets of links
                ->assertPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
                ->assertPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')

                // Click either of the page 10 links, it doesn't matter which
                ->waitForLivewire()->click('[wire\\:click="gotoPage(10, \'page\')"]')

                /**
                 * Typically it is the first set of links that break due to morphdom
                 * So we will test the second set of links first, to make sure everything is ok
                 * Then we will check the first set of links
                 */
                ->assertNotPresent('[dusk="second-links"] [wire\\:click="gotoPage(6, \'page\')"]')
                ->assertNotPresent('[dusk="first-links"] [wire\\:click="gotoPage(6, \'page\')"]')
                ;
        });
    }

    /** @test */
    public function it_calls_pagination_hook_method_when_pagination_changes()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ComponentWithPaginationHook::class)
                /**
                 * Test that going to page 2, then back to page 1 removes "page" from the query string.
                 */
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
        });
    }

    /** @test */
    public function it_can_have_two_pagination_instances_on_a_page_tailwind()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ComponentWithTwoPaginatorsTailwind::class)
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
        });
    }

    /** @test */
    public function it_can_have_two_pagination_instances_on_a_page_bootstrap()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ComponentWithTwoPaginatorsBootstrap::class)
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
        });
    }

    /** @test */
    public function it_calls_pagination_hook_methods_when_pagination_changes_with_multiple_paginators()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ComponentWithTwoPaginatorsTailwind::class)
                // ->tinker()
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
        });
    }

    /** @test */
    public function pagination_trait_doesnt_overwrite_query_string_from_component()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, PaginationComponentWithCustomQueryString::class)
                /**
                 * Test that going to page 2 removes "page" from the query string due to the custom "except" in the component.
                 */
                ->assertSee('Post #1')
                ->assertSee('Post #2')
                ->assertSee('Post #3')
                ->assertDontSee('Post #4')
                ->assertQueryStringHas('page', '1')

                ->waitForLivewire()->click('@nextPage.before')

                ->assertDontSee('Post #3')
                ->assertSee('Post #4')
                ->assertSee('Post #5')
                ->assertSee('Post #6')
                ->assertQueryStringMissing('page')
            ;
        });
    }

    /** @test */
    public function pagination_trait_resolves_query_string_alias_for_page_from_component()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, PaginationComponentWithQueryStringAliasForPage::class, '?p=2')
                /**
                 * Test a deeplink to page 2 with "p" from the query string shows the second page.
                 */
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
        });
    }
}
