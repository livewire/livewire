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
}
