<?php

namespace LegacyTests\Browser\Confirm;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            // test wire:confirm
            $this->visitLivewireComponent($browser, Component::class)
                ->type('@confirmInput', 'foo')
                ->assertValue('@confirmInput', 'foo')
                ->click('@confirmSubmit')
                ->assertDialogOpened('please confirm')
                ->dismissDialog()
                ->pause(150)
                ->assertValue('@confirmInput', 'foo');

            // as the dialog was dismissed, the button should be interactive again
            $this->assertEquals(null, $browser->attribute('@confirmSubmit', 'disabled'));
            // and so should the input
            $this->assertEquals(null, $browser->attribute('@confirmSubmit', 'readonly'));
            $this->assertEquals(null, $browser->attribute('@confirmSubmit', 'disabled'));

            $browser->click('@confirmSubmit')
                ->assertDialogOpened('please confirm')
                ->acceptDialog()
                ->pause(200)
                ->assertValue('@confirmInput', 'confirmed');

            // Test wire:confirm.prompt
            $browser->type('@promptInput', 'foo')
                ->assertValue('@promptInput', 'foo')
                ->click('@promptSubmit')
                ->assertDialogOpened('type PROMPT')
                ->dismissDialog()
                ->pause(150)
                ->assertValue('@promptInput', 'foo');

            // as the dialog was dismissed, the button should be interactive again
            $this->assertEquals(null, $browser->attribute('@promptSubmit', 'disabled'));
            // and so should the input
            $this->assertEquals(null, $browser->attribute('@promptSubmit', 'readonly'));
            $this->assertEquals(null, $browser->attribute('@promptSubmit', 'disabled'));

            $browser->click('@promptSubmit')
                ->assertDialogOpened('type PROMPT')
                ->typeInDialog('PROMPT')
                ->acceptDialog()
                ->pause(200)
                ->assertValue('@promptInput', 'confirmed');

            //sleep(10);
        });
    }
}
