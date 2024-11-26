<?php

namespace LegacyTests\Browser\QueryString;

use Sushi\Sushi;
use LegacyTests\Browser\TestCase;
use Laravel\Dusk\Browser;
use Illuminate\Database\Eloquent\Model;

class Test extends TestCase
{
    public function test_core_query_string_pushstate_logic()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [Component::class, 'nested' => NestedComponent::class], '?foo=baz&eoo=lob')
                /*
                 * Check that the intial property value is set from the query string.
                 */
                ->assertSeeIn('@output', 'baz')
                ->assertInputValue('@input', 'baz')

                /*
                 * Check that Livewire doesn't mess with query string order.
                 */
                ->assertScript('return !! window.location.search.match(/foo=baz&eoo=lob/)')

                /**
                 * Change a property and see it reflected in the query string.
                 */
                ->waitForLivewire()->type('@input', 'bob')
                ->assertSeeIn('@output', 'bob')
                ->assertInputValue('@input', 'bob')
                ->assertQueryStringHas('foo', 'bob')

                /**
                 * Hit the back button and see the change reflected in both
                 * the query string AND the actual property value.
                 */
                ->waitForLivewire()->back()
                ->assertSeeIn('@output', 'baz')
                ->assertQueryStringHas('foo', 'baz')

                /**
                 * Setting a property BACK to it's original value
                 * removes the property entirely from the query string.
                 * As long as it wasn't there to begin with...
                 */
                ->assertSeeIn('@bar-output', 'baz')
                ->waitForLivewire()->type('@bar-input', 'new-value')
                ->assertQueryStringHas('bar')
                ->waitForLivewire()->type('@bar-input', 'baz')
                ->assertQueryStringMissing('bar')

                /**
                 * Add a nested component on the page and make sure
                 * both components play nice with each other.
                 */
                ->assertQueryStringMissing('baz')
                ->waitForLivewire()->click('@show-nested')
                ->pause(25)
                ->assertSeeIn('@baz-output', 'bop')
                ->waitForLivewire()->type('@baz-input', 'lop')
                ->assertQueryStringHas('baz', 'lop')
                ->waitForLivewire()->type('@input', 'plop')
                ->waitForLivewire()->type('@baz-input', 'ploop')
                ->assertQueryStringHas('foo', 'plop')
                ->assertQueryStringHas('baz', 'ploop')
                ->back()
                ->back()
                ->assertQueryStringHas('baz', 'lop')

                /**
                 * Can change an array property.
                 */
                ->assertSeeIn('@bob.output', '["foo","bar"]')
                ->waitForLivewire()->click('@bob.modify')
                ->assertSeeIn('@bob.output', '["foo","bar","baz"]')
                ->refresh()
                ->assertSeeIn('@bob.output', '["foo","bar","baz"]')
            ;
        });
    }

    public function test_query_string_format_in_rfc_1738()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->waitForLivewire()->type('@input', 'foo bar')
                ->assertSeeIn('@output', 'foo bar')
                ->assertInputValue('@input', 'foo bar')
                ->assertQueryStringHas('foo', 'foo bar')
                ->assertScript('return !! window.location.search.match(/foo=foo\+bar/)')
                ->assertScript('return !! window.location.search.match(/quux%26quuz/)')
            ;
        });
    }

    public function test_query_string_with_rfc_1738_bookmarked_url()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?qux[hyphen]=quux-quuz&qux[comma]=quux,quuz&qux[ampersand]=quux%26quuz&qux[space]=quux+quuz&qux[array][0]=quux&qux[array][1]=quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@qux.hyphen', 'quux-quuz')
                ->assertSeeIn('@qux.comma', 'quux,quuz')
                ->assertSeeIn('@qux.ampersand', 'quux&quuz')
                ->assertSeeIn('@qux.space', 'quux quuz')
                ->assertSeeIn('@qux.array', '["quux","quuz"]')
            ;
        });
    }

    public function test_query_string_with_rfc_3986_bookmarked_url_forbackwards_compatibility()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?qux%5Bhyphen%5D=quux-quuz&qux%5Bcomma%5D=quux%2Cquuz&qux%5Bampersand%5D=quux%26quuz&qux%5Bspace%5D=quux%20quuz&qux%5Barray%5D%5B%5D=quux&qux%5Barray%5D%5B%5D=quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@qux.hyphen', 'quux-quuz')
                ->assertSeeIn('@qux.comma', 'quux,quuz')
                ->assertSeeIn('@qux.ampersand', 'quux&quuz')
                ->assertSeeIn('@qux.space', 'quux quuz')
                ->assertSeeIn('@qux.array', '["quux","quuz"]')
            ;
        });
    }

    public function test_query_string_with_property_values_containing_ampersand_characters()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?foo=bar%26quux%26quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@output', 'bar&quux&quuz')
                ->refresh()
                ->assertSeeIn('@output', 'bar&quux&quuz')
            ;
        });
    }

    public function test_back_button_after_refresh_works_with_nested_components()
    {
        $this->markTestSkipped(); // @todo: fix this...

        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [Component::class, 'nested' => NestedComponent::class])
                ->waitForLivewire()->click('@show-nested')
                ->waitForLivewire()->type('@baz-input', 'foo')
                ->assertSeeIn('@baz-output', 'foo')
                ->refresh()
                ->back()
                ->forward()
                ->assertSeeIn('@baz-output', 'foo')

                // Interact with the page again to make sure everything still works.
                ->waitForLivewire()->type('@baz-input', 'bar')
                ->assertSeeIn('@baz-output', 'bar')
            ;
        });
    }

    // public function test_excepts_results_in_no_query_string()
    // {
    //     $this->browse(function (Browser $browser) {
    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class)
    //             ->assertSeeIn('@output', 'On page 1');

    //         $this->assertStringNotContainsString('?', $browser->driver->getCurrentURL());

    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class, '?page=1')
    //             ->assertSeeIn('@output', 'On page 1');

    //         $this->assertStringNotContainsString('?', $browser->driver->getCurrentURL());

    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class, '?page=2')
    //             ->assertSeeIn('@output', 'On page 2')
    //             ->assertQueryStringHas('page', 2);
    //     });
    // }

    public function test_that_input_values_are_set_after_back_button()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, DirtyDataComponent::class)
                ->waitForLivewire()->type('@input', 'foo')
                ->waitForLivewire()->click('@nextPage')
                ->assertSee('The Next Page')
                ->back()
                ->waitFor('@input')
                ->assertInputValue('@input', 'foo')
                ->forward()
                ->back()
                ->waitFor('@input')
                ->assertInputValue('@input', 'foo')
            ;
        });
    }

    public function test_dynamic_query_string_method()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithMethodInsteadOfProperty::class)
                ->assertQueryStringHas('foo', 'bar')
            ;
        });
    }

    public function test_nested_component_query_string_works_when_parent_is_not_using_query_string()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [ParentComponentWithNoQueryString::class, 'nested' => NestedComponent::class])
                ->assertPathBeginsWith('/livewire-dusk')
                ->waitForLivewire()->click('@toggle-nested')

                // assert the path hasn't change to /livewire/message
                ->assertPathBeginsWith('/livewire-dusk')
                ->assertQueryStringHas('baz', 'bop')
            ;
        });
    }

    public function test_it_does_not_build_query_string_from_referer_if_it_is_coming_from_a_full_page_redirect()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, RedirectLinkToQueryStringComponent::class)
                ->assertPathBeginsWith('/livewire-dusk/LegacyTests%5CBrowser%5CQueryString%5CRedirectLinkToQueryStringComponent')
                ->click('@link')

                ->pause(200)
                // assert the path has changed to new component path
                ->assertPathBeginsWith('/livewire-dusk/LegacyTests%5CBrowser%5CQueryString%5CNestedComponent')
                ->assertQueryStringHas('baz', 'bop')
            ;
        });
    }

    public function test_query_string_hooks_from_traits()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithTraits::class)
                ->assertSee('Post #1')
                ->assertSee('Post #2')
                ->assertSee('Post #3')
                ->assertQueryStringMissing('search')
                // Search for posts where title contains "1".
                ->waitForLivewire()->type('@search', '1')
                ->assertSee('Post #1')
                ->assertSee('Post #10')
                ->assertSee('Post #11')
                ->assertDontSee('Post #2')
                ->assertDontSee('Post #3')
                ->assertQueryStringHas('search', '1')
                // Search for posts where title contains "42".
                ->waitForLivewire()->type('@search', '42')
                ->assertSee('Post #42')
                ->assertQueryStringHas('search', '42')
            ;
        });
    }

    public function test_query_string_aliases()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithAliases::class)
                ->assertQueryStringMissing('s')
                // Search for posts where title contains "1".
                ->waitForLivewire()->type('@search', '1')
                ->assertQueryStringHas('s', '1')
                ->assertInputValue('@search', '1')
                // Search for posts where title contains "qwerty".
                ->waitForLivewire()->type('@search', 'qwerty')
                ->assertQueryStringHas('s', 'qwerty')
                ->assertInputValue('@search', 'qwerty')
            ;
        });
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
