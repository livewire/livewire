<?php

namespace Tests\Browser\Pagination;

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
                ->assertPresent('[dusk="first-links"] [wire\\:click="gotoPage(6)"]')
                ->assertPresent('[dusk="second-links"] [wire\\:click="gotoPage(6)"]')

                // Click either of the page 10 links, it doesn't matter which
                ->waitForLivewire()->click('[wire\\:click="gotoPage(10)"]')

                /**
                 * Typically it is the first set of links that break due to morphdom
                 * So we will test the second set of links first, to make sure everything is ok
                 * Then we will check the first set of links
                 */
                ->assertNotPresent('[dusk="second-links"] [wire\\:click="gotoPage(6)"]')
                ->assertNotPresent('[dusk="first-links"] [wire\\:click="gotoPage(6)"]')
                ;
        });
    }

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
}
