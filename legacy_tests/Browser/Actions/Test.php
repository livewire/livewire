<?php

namespace LegacyTests\Browser\Actions;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{

    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Basic action (click).
                 */
                ->waitForLivewire()->click('@foo')
                ->assertSeeIn('@output', 'foo')
                /**
                 * Action with params.
                 */
                ->waitForLivewire()->click('@bar')
                ->assertSeeIn('@output', 'barbell')
                /**
                 * Action with various parameter formatting differences.
                 */
                ->waitForLivewire()->click('@ball')
                ->assertSeeIn('@output', 'abcdef')
                /**
                 * Action with no params, but still parenthesis.
                 */
                ->waitForLivewire()->click('@bowl')
                ->assertSeeIn('@output', 'foo')
                /**
                 * Action with no params, but still parenthesis and having some spaces.
                 */
                ->waitForLivewire()->click('@baw')
                ->assertSeeIn('@output', 'foo')
                /**
                 * Action on multiple lines
                 */
                ->waitForLivewire()->click('@fizzfuzz')
                ->assertSeeIn('@output', 'fizzfuzz')
                /**
                 * wire:click.self
                 */
                ->waitForLivewire()->click('@baz.inner')
                ->assertSeeIn('@output', 'fizzfuzz')
                ->waitForLivewire()->click('@baz.outer')
                ->assertSeeIn('@output', 'baz')
                /**
                 * Blur event and click event get sent together
                 */
                ->click('@bop.input') // Fucus.
                ->assertSeeIn('@output', 'baz')
                ->waitForLivewire()->click('@bop.button')
                ->assertSeeIn('@output', 'bazbopbop')
                /**
                 * Two keydowns
                 */
                ->waitForLivewire()->keys('@bob', '{enter}')
                ->assertSeeIn('@output', 'bazbopbopbobbob')
                /**
                 * If listening for "enter", other keys don't trigger the action.
                 */
                ->keys('@lob', 'k')
                ->pause(150)
                ->assertDontSeeIn('@output', 'lob')
                ->waitForLivewire()->keys('@lob', '{enter}')
                ->assertSeeIn('@output', 'lob')
                /**
                 * keydown.shift.enter
                 */
                ->waitForLivewire()->keys('@law', '{shift}', '{enter}')
                ->assertSeeIn('@output', 'law')
                /**
                 * keydown.space
                 */
                ->waitForLivewire()->keys('@spa', '{space}')
                ->assertSeeIn('@output', 'spa')
                /**
                 * Elements are marked as read-only during form submission and form as data-submitting
                 */
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.form', 'data-submitting'));
                    $this->assertNull($b->attribute('@blog.input', 'readonly'));
                    $this->assertNull($b->attribute('@blog.input.ignored', 'readonly'));
                })
                ->waitForLivewire(function ($b) {
                    $b->assertSeeIn('@times_rendered', '14');
                    $b->press('@blog.button');
                    $b->press('@blog.button'); // Second click that will be blocked

                    $this->assertEquals('true', $b->attribute('@blog.form', 'data-submitting'));
                    $this->assertEquals('true', $b->attribute('@blog.input', 'readonly'));
                    $this->assertNull($b->attribute('@blog.input.ignored', 'readonly'));
                })
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.form', 'data-submitting'));
                    $this->assertNull($b->attribute('@blog.input', 'readonly'));

                    // Check if the second click has been blocked by form attribute data-submitting
                    $b->assertSeeIn('@times_rendered', '15');
                })
                /**
                 * Form are un-marked as data-submitting when form errors out.
                 */
                ->press('@boo.button')
                ->tap(function ($b) {
                    $this->assertEquals('true', $b->attribute('@boo.form', 'data-submitting'));
                })
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.form', 'data-submitting'));
                })
                ->waitFor('#livewire-error')
                ->click('#livewire-error')
                /**
                 * keydown.debounce
                 */
                ->keys('@bap', 'x')
                ->pause(50)
                ->waitForLivewire()->assertDontSeeIn('@output', 'bap')
                ->assertSeeIn('@output', 'bap');
        });
    }
}
