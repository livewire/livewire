<?php

namespace Tests\Browser\Actions;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Basic action (click).
                 */
                ->click('@foo')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'foo')

                /**
                 * Action with params.
                 */
                ->click('@bar')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'barbell')

                /**
                 * Action with various parameter formatting differences.
                 */
                ->click('@ball')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'abcdef')

                /**
                 * Action with no params, but still parenthesis.
                 */
                ->click('@bowl')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'foo')

                /**
                 * wire:click.self
                 */
                ->click('@baz.inner')->waitForLivewire()
                ->assertSeeIn('@output', 'foo')
                ->click('@baz.outer')->waitForLivewire()
                ->assertSeeIn('@output', 'baz')

                /**
                 * Blur event and click event get sent together
                 */
                ->click('@bop.input') // Fucus.
                ->assertSeeIn('@output', 'baz')
                ->click('@bop.button')->waitForLivewire()
                ->assertSeeIn('@output', 'bazbopbop')

                /**
                 * Two keydowns
                 */
                ->keys('@bob', '{enter}')->waitForLivewire()
                ->assertSeeIn('@output', 'bazbopbopbobbob')

                /**
                 * If listening for "enter", other keys don't trigger the action.
                 */
                ->keys('@lob', 'k')
                ->pause(150)
                ->assertDontSeeIn('@output', 'lob')
                ->keys('@lob', '{enter}')->waitForLivewire()
                ->assertSeeIn('@output', 'lob')

                /**
                 * keydown.shift.enter
                 */
                ->keys('@law', '{shift}', '{enter}')->waitForLivewire()
                ->assertSeeIn('@output', 'law')

                /**
                 * Elements are marked as read-only during form submission
                 */
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.button', 'disabled'));
                    $this->assertNull($b->attribute('@blog.input', 'readonly'));
                    $this->assertNull($b->attribute('@blog.input.ignored', 'readonly'));
                })
                ->press('@blog.button')
                ->waitForLivewireRequest()
                ->tap(function ($b) {
                    $this->assertEquals('true', $b->attribute('@blog.button', 'disabled'));
                    $this->assertEquals('true', $b->attribute('@blog.input', 'readonly'));
                    $this->assertNull($b->attribute('@blog.input.ignored', 'readonly'));
                })
                ->waitForLivewireResponse()
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.button', 'disabled'));
                    $this->assertNull($b->attribute('@blog.input', 'readonly'));
                })

                /**
                 * Elements are un-marked as readonly when form errors out.
                 */
                ->press('@boo.button')
                ->waitForLivewireRequest()
                ->tap(function ($b) {
                    $this->assertEquals('true', $b->attribute('@boo.button', 'disabled'));
                })
                ->waitForLivewireResponse()
                ->tap(function ($b) {
                    $this->assertNull($b->attribute('@blog.button', 'disabled'));
                })
                ->click('#livewire-error')

                /**
                 * keydown.debounce
                 */
                ->keys('@bap', 'x')
                ->pause(50)
                ->assertDontSeeIn('@output', 'bap')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'bap')
            ;
        });
    }
}
