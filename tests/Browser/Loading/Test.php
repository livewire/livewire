<?php

namespace Tests\Browser\Loading;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Loading\Component;

class Test extends TestCase
{
    /** @test */
    public function loading_indicator()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tap(function ($browser) {
                    $browser->assertNotVisible('@show');
                    $browser->assertVisible('@hide');

                    $this->assertEquals('', $browser->resolver->find('@add-class')->getAttribute('class'));
                    $this->assertEquals('foo', $browser->resolver->find('@remove-class')->getAttribute('class'));

                    $this->assertEquals('', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('true', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $this->assertEquals('', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('true', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $browser->assertNotVisible('@targeting');
                })
                ->click('@button')
                ->waitForLivewireRequest()
                ->tap(function ($browser) {
                    $browser->assertVisible('@show');
                    $browser->assertNotVisible('@hide');

                    $this->assertEquals('foo', $browser->resolver->find('@add-class')->getAttribute('class'));
                    $this->assertEquals('', $browser->resolver->find('@remove-class')->getAttribute('class'));

                    $this->assertEquals('true', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $this->assertEquals('true', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $browser->assertNotVisible('@targeting');
                })
                ->waitForLivewireResponse()
                ->tap(function ($browser) {
                    $browser->assertNotVisible('@show');
                    $browser->assertVisible('@hide');

                    $this->assertEquals('', $browser->resolver->find('@add-class')->getAttribute('class'));
                    $this->assertEquals('foo', $browser->resolver->find('@remove-class')->getAttribute('class'));

                    $this->assertEquals('', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('true', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $this->assertEquals('', $browser->resolver->find('@add-attr')->getAttribute('disabled'));
                    $this->assertEquals('true', $browser->resolver->find('@remove-attr')->getAttribute('disabled'));

                    $browser->assertNotVisible('@targeting');
                })
                ->click('@target-button')
                ->waitForLivewireRequest()
                ->tap(function ($browser) {
                    $browser->assertVisible('@targeting');
                });
        });
    }
}
