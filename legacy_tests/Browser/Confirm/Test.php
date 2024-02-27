<?php

namespace LegacyTests\Browser\Confirm;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->type('@input', 'foo')
                ->assertValue('@input', 'foo')
                ->click('@submit')
                ->assertDialogOpened('please confirm')
                ->dismissDialog()
                ->pause(150)
                ->assertValue('@input', 'foo');

            // as the dialog was dismissed, the button should be interactive again
            $this->assertEquals(null, $browser->attribute('@submit', 'disabled'));
            // and so should the input
            $this->assertEquals(null, $browser->attribute('@submit', 'readonly'));
            $this->assertEquals(null, $browser->attribute('@submit', 'disabled'));

            $browser->click('@submit')
                ->assertDialogOpened('please confirm')
                ->acceptDialog()
                ->pause(150)
                ->assertValue('@input', 'confirmed');
        });
    }
}
